#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-http://localhost:8080}"
BASE_URL="${BASE_URL%/}"

echo "Checking app health at ${BASE_URL}/health.php"
curl -fsS "${BASE_URL}/health.php" >/dev/null

echo "Checking database-backed readiness at ${BASE_URL}/health.php?deep=1"
curl -fsS "${BASE_URL}/health.php?deep=1" >/dev/null

echo "Checking home page"
curl -fsS "${BASE_URL}/" | grep -q "CNAS Assignment"

if [[ "${RUN_CRUD:-0}" == "1" ]]; then
    EMAIL="smoke-$(date +%s)@example.edu"
    echo "Creating smoke-test member ${EMAIL}"
    curl -fsS -X POST \
        -d "name=Smoke Test" \
        -d "email=${EMAIL}" \
        "${BASE_URL}/create.php" >/dev/null
    curl -fsS "${BASE_URL}/" | grep -q "${EMAIL}"
fi

echo "Smoke test passed."

