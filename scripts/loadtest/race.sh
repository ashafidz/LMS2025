#!/usr/bin/env bash
# Race test: one student sends 20 identical requests at the same moment (complete lesson, begin quiz, submit quiz),
# then the database counts are printed. Same server setup as run.sh (MySQL database lms_loadtest, 20 workers).
# Usage: scripts/loadtest/race.sh [parallel=20]
set -euo pipefail
cd "$(dirname "$0")/../.."
LOADTEST_SCRIPT=race.js LOADTEST_PARALLEL="${1:-20}" LOADTEST_REPORT=scripts/loadtest/race-report.php \
    exec scripts/loadtest/run.sh after 1
