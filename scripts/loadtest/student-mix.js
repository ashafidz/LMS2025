// k6 load test: N students doing random student activity at the same time.
// Run through scripts/loadtest/run.sh (it seeds the data and starts a server that mimics the hosting limits).
import http from 'k6/http';
import { check, sleep } from 'k6';
import { parseHTML } from 'k6/html';

const BASE = __ENV.BASE_URL || 'http://127.0.0.1:8080';
const VUS = parseInt(__ENV.VUS || '150', 10);
const HOLD = __ENV.HOLD || '3m';
const fx = JSON.parse(open(__ENV.FIXTURES));

// ~25 KB fake JPEG, about the size of a 480px / 0.6 quality screenshot
const SCREENSHOT = new Uint8Array(25 * 1024).buffer;

const NAMES = [
    'login_page', 'login', 'home', 'catalog', 'course_public', 'course_page', 'lesson_content',
    'lesson_complete', 'leaderboard', 'quiz_start', 'quiz_begin', 'camera_violation', 'tab_violation', 'quiz_submit',
];

const thresholds = { http_req_failed: ['rate<0.01'] };
for (const name of NAMES) {
    // Informational thresholds: they make k6 report p95 and failures per request type
    thresholds[`http_req_duration{name:${name}}`] = ['p(95)<3000'];
    thresholds[`http_req_failed{name:${name}}`] = ['rate<0.01'];
}

export const options = {
    scenarios: {
        students: {
            executor: 'ramping-vus',
            startVUs: 0,
            stages: [
                { duration: '1m', target: VUS },
                { duration: HOLD, target: VUS },
                { duration: '20s', target: 0 },
            ],
            gracefulRampDown: '30s',
        },
    },
    thresholds,
    noCookiesReset: true, // keep each student's session between iterations (they log in once)
    summaryTrendStats: ['avg', 'med', 'p(95)', 'max'],
};

// Per-VU state (each VU has its own JS runtime)
let loggedIn = false;
let csrf = null;

// DEBUG=1 prints every failed response (status, request type, URL)
http.setResponseCallback(http.expectedStatuses({ min: 200, max: 399 }));
function logFailure(res) {
    if (__ENV.DEBUG && res && res.status >= 400) {
        console.warn(`${res.status} ${res.request.method} ${res.url} ${String(res.body).substring(0, 120).replace(/\s+/g, ' ')}`);
    }
    return res;
}

const pick = (list) => list[Math.floor(Math.random() * list.length)];
const think = (min, max) => sleep(min + Math.random() * (max - min));

function get(path, name, extra = {}) {
    return logFailure(http.get(BASE + path, { tags: { name }, ...extra }));
}

function ajaxHeaders() {
    return { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' };
}

function login() {
    const email = fx.emails[(__VU - 1) % fx.emails.length];
    const page = get('/login', 'login_page');
    const token = parseHTML(page.body).find('input[name=_token]').attr('value');
    const res = http.post(BASE + '/login', { _token: token, email, password: fx.password }, { tags: { name: 'login' } });
    loggedIn = check(res, { 'logged in': (r) => r.status === 200 && !r.url.endsWith('/login') });
}

function coursePage() {
    const res = get(fx.course_page, 'course_page');
    if (res.status === 200) {
        csrf = parseHTML(res.body).find('meta[name=csrf-token]').attr('content') || csrf;
    }
    check(res, { 'course page 200': (r) => r.status === 200 });
}

function quizFlow() {
    get(fx.quiz_start, 'quiz_start');
    think(3, 6);

    const take = logFailure(http.post(BASE + fx.quiz_begin, { _token: csrf }, { tags: { name: 'quiz_begin' } }));
    if (!check(take, { 'quiz take page': (r) => r.status === 200 && r.body.includes('quiz-form') })) {
        return;
    }

    const doc = parseHTML(take.body);
    const action = doc.find('#quiz-form').attr('action');
    const cameraUrl = (take.body.match(/fetch\('([^']*log-camera-violation)'/) || [])[1];
    const tabUrl = (take.body.match(/fetch\('([^']*log-tab-violation)'/) || [])[1];

    // Pick a random option per question
    const answers = {};
    doc.find('input[type=radio]').each((_, el) => {
        const name = el.attributes().name && el.attributes().name.value;
        if (name && (!(name in answers) || Math.random() < 0.3)) {
            answers[name] = el.attributes().value.value;
        }
    });

    // Answering takes time; about a third of the students trigger proctoring reports meanwhile
    const reportsViolations = Math.random() < 0.33;
    for (let i = 0; i < 4; i++) {
        think(8, 15);
        if (reportsViolations && cameraUrl) {
            http.post(cameraUrl, {
                violation_type: pick(['look_down', 'look_left', 'face_not_detected']),
                screenshot: http.file(SCREENSHOT, 'violation.jpg', 'image/jpeg'),
            }, { headers: { 'X-CSRF-TOKEN': csrf }, tags: { name: 'camera_violation' } });
        }
        if (reportsViolations && tabUrl && Math.random() < 0.3) {
            http.post(tabUrl, JSON.stringify({ timestamp: new Date().toISOString() }), {
                headers: { ...ajaxHeaders(), 'Content-Type': 'application/json' },
                tags: { name: 'tab_violation' },
            });
        }
    }

    const res = logFailure(http.post(action, { _token: csrf, ...answers }, { tags: { name: 'quiz_submit' } }));
    check(res, { 'quiz submitted': (r) => r.status === 200 });
}

export default function () {
    if (!loggedIn) {
        login();
        if (!loggedIn) {
            think(5, 10);
            return;
        }
        coursePage();
        think(2, 5);
    }

    const roll = Math.random() * 100;
    if (roll < 30) {
        const res = get(pick(fx.lesson_content), 'lesson_content', { headers: ajaxHeaders() });
        check(res, { 'lesson content 200': (r) => r.status === 200 });
    } else if (roll < 50) {
        coursePage();
    } else if (roll < 60) {
        const res = logFailure(http.post(BASE + pick(fx.lesson_complete), null, { headers: ajaxHeaders(), tags: { name: 'lesson_complete' } }));
        check(res, { 'lesson complete ok': (r) => r.status === 200 || r.status === 409 || r.status === 422 });
    } else if (roll < 65) {
        get(pick([fx.course_leaderboard, fx.module_leaderboard]), 'leaderboard', { headers: ajaxHeaders() });
    } else if (roll < 80) {
        quizFlow();
    } else if (roll < 95) {
        get(pick([fx.home, fx.catalog]), pick(['home', 'catalog']));
    } else {
        get(fx.course_public, 'course_public');
    }

    think(3, 8);
}

export function handleSummary(data) {
    const rows = [];
    const pad = (s, n) => String(s).padEnd(n);
    rows.push(`${pad('request', 18)}${pad('count', 8)}${pad('median ms', 11)}${pad('p95 ms', 10)}${pad('max ms', 10)}failed`);
    for (const name of NAMES) {
        const d = data.metrics[`http_req_duration{name:${name}}`];
        const f = data.metrics[`http_req_failed{name:${name}}`];
        if (!d || !d.values || !d.values.max) continue;
        const count = f && f.values ? f.values.passes + f.values.fails : '-';
        const failed = f && f.values ? `${(f.values.rate * 100).toFixed(1)}%` : '-';
        rows.push(`${pad(name, 18)}${pad(count, 8)}${pad(Math.round(d.values.med), 11)}${pad(Math.round(d.values['p(95)']), 10)}${pad(Math.round(d.values.max), 10)}${failed}`);
    }
    const total = data.metrics.http_reqs.values;
    const failedAll = (data.metrics.http_req_failed.values.rate * 100).toFixed(2);
    rows.push('');
    rows.push(`total requests: ${total.count} (${total.rate.toFixed(1)}/s), failed: ${failedAll}%, max VUs: ${data.metrics.vus_max.values.max}`);
    const text = rows.join('\n') + '\n';
    const out = {};
    out.stdout = '\n' + text;
    if (__ENV.SUMMARY_FILE) {
        out[__ENV.SUMMARY_FILE] = text;
    }
    return out;
}
