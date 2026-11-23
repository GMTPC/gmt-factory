#!/bin/sh
set -eu

cd /app

lock_hash="$(sha256sum package-lock.json | cut -d ' ' -f 1)"
saved_hash="$(cat node_modules/.dev-lock-hash 2>/dev/null || true)"

if [ "$lock_hash" != "$saved_hash" ]; then
    npm ci --no-audit --no-fund
    printf '%s' "$lock_hash" > node_modules/.dev-lock-hash
fi

exec npm run dev -- --host 0.0.0.0
