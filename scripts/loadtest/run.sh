#!/usr/bin/env bash
# Local load test that mimics the DomaiNesia limits: PHP pinned to 1 CPU core, 20 PHP workers (= 20 entry processes).
#
# Usage: scripts/loadtest/run.sh [before|after] [students] [hold]
#   before = current production config (database session/cache, bcrypt 12, debug log)
#   after  = Phase 0 config (file session/cache, bcrypt 10, warning log)
#   students defaults to 150, hold (time at full load) defaults to 3m
#
# Needs: a local MySQL/MariaDB with an empty database (default lms_loadtest) that the .env DB user can use,
# and k6 (~/.local/bin/k6). The database is wiped (migrate:fresh) on every run.
set -euo pipefail

MODE="${1:-after}"
STUDENTS="${2:-150}"
HOLD="${3:-3m}"
PORT="${LOADTEST_PORT:-8080}"
WORKERS="${LOADTEST_WORKERS:-20}"
CPU="${LOADTEST_CPU:-0}"
K6="${K6:-$(command -v k6 || echo "$HOME/.local/bin/k6")}"

cd "$(dirname "$0")/../.."
ROOT="$(pwd)"

case "$MODE" in
    before) export SESSION_DRIVER=database CACHE_STORE=database BCRYPT_ROUNDS=12 LOG_LEVEL=debug LOG_STACK=single ;;
    after) export SESSION_DRIVER=file CACHE_STORE=file BCRYPT_ROUNDS=10 LOG_LEVEL=warning LOG_STACK=daily ;;
    *) echo "Mode must be 'before' or 'after'"; exit 1 ;;
esac

# Environment variables win over .env, so the dev database and settings are left alone
export APP_ENV=loadtest APP_DEBUG=false APP_URL="http://127.0.0.1:${PORT}" SESSION_SECURE_COOKIE=false  # plain HTTP locally
export DB_DATABASE="${LOADTEST_DB:-lms_loadtest}" MAIL_MAILER=log QUEUE_CONNECTION=database
export LOADTEST_STUDENTS="$STUDENTS"

PHP_OPTS=(-d zend_extension=opcache -d opcache.enable_cli=1 -d opcache.validate_timestamps=0 -d memory_limit=256M)

cleanup() {
    [[ -n "${SERVER_PID:-}" ]] && kill "$SERVER_PID" 2>/dev/null || true
    # Do not leave a cached config that points at the load test database
    php artisan optimize:clear >/dev/null 2>&1 || true
}
trap cleanup EXIT

echo "==> Mode: $MODE | students: $STUDENTS | hold: $HOLD | workers: $WORKERS on CPU core $CPU"
php artisan optimize:clear >/dev/null
php artisan migrate:fresh --force >/dev/null
php artisan db:seed --class=LoadTestSeeder --force
php artisan optimize >/dev/null   # config, route, view and event cache, like the deploy

# Laravel's router script uses the working directory as the public path, so start it from public/
(cd public && PHP_CLI_SERVER_WORKERS="$WORKERS" exec taskset -c "$CPU" php "${PHP_OPTS[@]}" -S "127.0.0.1:${PORT}" \
    "$ROOT/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php" >/dev/null 2>&1) &
SERVER_PID=$!
sleep 2
curl -fsS "http://127.0.0.1:${PORT}/up" | grep -q "Application up" || { echo "Server did not start"; exit 1; }

mkdir -p storage/logs/loadtest
SCRIPT="${LOADTEST_SCRIPT:-student-mix.js}"
SUMMARY="storage/logs/loadtest/$(date +%Y%m%d-%H%M%S)-${MODE}-${STUDENTS}.txt"
"$K6" run --quiet \
    -e BASE_URL="http://127.0.0.1:${PORT}" -e VUS="$STUDENTS" -e HOLD="$HOLD" -e PARALLEL="${LOADTEST_PARALLEL:-20}" \
    -e DEBUG="${LOADTEST_DEBUG:-}" -e FIXTURES="$ROOT/storage/app/loadtest-fixtures.json" -e SUMMARY_FILE="$ROOT/$SUMMARY" \
    "scripts/loadtest/$SCRIPT" || true

if [[ -n "${LOADTEST_REPORT:-}" ]]; then
    # Extra report (race.sh): runs with the same load test environment
    php artisan tinker --execute="require base_path('${LOADTEST_REPORT}');"
elif [[ -f "$SUMMARY" ]]; then
    echo "==> Summary saved to $SUMMARY"
fi
