// k6 race test: the same student sends N identical requests at the same moment.
// Run through scripts/loadtest/race.sh, which prints the resulting database counts.
import http from 'k6/http';
import { sleep } from 'k6';
import { parseHTML } from 'k6/html';

const BASE = __ENV.BASE_URL || 'http://127.0.0.1:8080';
const PARALLEL = parseInt(__ENV.PARALLEL || '20', 10);
const fx = JSON.parse(open(__ENV.FIXTURES));

export const options = {
    scenarios: { race: { executor: 'per-vu-iterations', vus: PARALLEL, iterations: 1, maxDuration: '2m' } },
    noCookiesReset: true,
};

// Log in once (Fortify allows 5 logins per minute per email); every VU then reuses this one session,
// like a double click from the same browser
function login() {
    const page = http.get(`${BASE}/login`);
    const token = parseHTML(page.body).find('input[name=_token]').attr('value');
    http.post(`${BASE}/login`, { _token: token, email: fx.emails[0], password: fx.password });
    const course = http.get(BASE + fx.course_page);
    return parseHTML(course.body).find('meta[name=csrf-token]').attr('content');
}

function waitUntil(timestamp) {
    const ms = timestamp - Date.now();
    if (ms > 0) sleep(ms / 1000);
}

export function setup() {
    // One attempt on the main quiz, submitted by all VUs at once in step C
    const csrf = login();
    const take = http.post(BASE + fx.quiz_begin, { _token: csrf });
    const doc = parseHTML(take.body);
    const answers = {};
    doc.find('input[type=radio]').each((_, el) => {
        const name = el.attributes().name.value;
        if (!(name in answers)) answers[name] = el.attributes().value.value;
    });

    const cookies = http.cookieJar().cookiesForURL(BASE);

    return { startAt: Date.now() + 5000, action: doc.find('#quiz-form').attr('action'), answers, csrf, cookies };
}

export default function (data) {
    const jar = http.cookieJar();
    for (const [name, values] of Object.entries(data.cookies)) {
        jar.set(BASE, name, values[0]);
    }
    const csrf = data.csrf;
    const ajax = { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' };

    // A: mark the same lesson complete
    waitUntil(data.startAt);
    const a = http.post(BASE + fx.lesson_complete[0], null, { headers: ajax, tags: { name: 'A_complete' } });

    // B: begin the same quiz (max_attempts = 1)
    waitUntil(data.startAt + 4000);
    const b = http.post(BASE + fx.race_quiz_begin, { _token: csrf }, { redirects: 0, tags: { name: 'B_begin' } });

    // C: submit the same attempt
    waitUntil(data.startAt + 8000);
    const c = http.post(data.action, { _token: csrf, ...data.answers }, { redirects: 0, tags: { name: 'C_submit' } });

    console.log(`statuses A=${a.status} B=${b.status} C=${c.status}`);
}
