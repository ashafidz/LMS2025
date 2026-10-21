#!/usr/bin/env bash
# One-command verification for this Laravel project. Mirrors .github/workflows/ci.yml.
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

echo "========================================"
echo " Running Project Sanity Verification    "
echo "========================================"

FAILED=0

run_step() {
  local name="$1"
  local cmd="$2"
  echo ""
  echo "--> Running: $name ($cmd)..."
  if eval "$cmd"; then
    echo "    [PASS] $name"
  else
    echo "    [FAIL] $name"
    FAILED=1
  fi
}

# 1. Code style (Laravel Pint)
run_step "Code Style (Pint)" "vendor/bin/pint --test"

# 2. Automated tests (PHPUnit, SQLite in-memory via phpunit.xml)
run_step "Tests (PHPUnit)" "php artisan config:clear --ansi >/dev/null && php artisan test"

# 3. Frontend build (Vite)
run_step "Build (Vite)" "npm run build"

echo ""
echo "========================================"
if [ "$FAILED" -eq 0 ]; then
  echo " ALL CHECKS PASSED. Ready for commit!  "
  echo "========================================"
  exit 0
else
  echo " CHECKS FAILED. Please fix the errors!  "
  echo "========================================"
  exit 1
fi
