#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
PHP_CMD="${PHP:-php}"
COMPOSER_CMD="${COMPOSER:-}"

cd "${APP_DIR}"

ENV_CREATED=0

if [ -z "${COMPOSER_CMD}" ]; then
    if command -v composer2 >/dev/null 2>&1; then
        COMPOSER_CMD="composer2"
    elif command -v composer >/dev/null 2>&1; then
        COMPOSER_CMD="composer"
    else
        echo "Composer tidak ditemukan. Set env COMPOSER=/path/to/composer lalu jalankan ulang."
        exit 1
    fi
fi

if [ ! -f ".env" ]; then
    cp .env.production.example .env
    ENV_CREATED=1
    echo ".env dibuat dari .env.production.example. Edit nilai APP_URL dan database sebelum akses aplikasi."
fi

${COMPOSER_CMD} install --no-dev --prefer-dist --optimize-autoloader --no-interaction

${PHP_CMD} artisan optimize:clear

if ! grep -q '^APP_KEY=base64:' .env; then
    ${PHP_CMD} artisan key:generate --force
fi

if [ "${ENV_CREATED}" -eq 1 ]; then
    echo "Deploy awal berhenti di sini. Edit backend/.env, lalu jalankan script ini lagi."
    exit 0
fi

if grep -Eq 'domainanda\.com|nama_database|username_database|password_database' .env; then
    echo "backend/.env masih berisi placeholder. Edit nilai domain dan database, lalu jalankan ulang."
    exit 1
fi

${PHP_CMD} artisan storage:link --force
${PHP_CMD} artisan migrate --force
${PHP_CMD} artisan config:cache
${PHP_CMD} artisan route:cache
${PHP_CMD} artisan view:cache

chmod -R ug+rw storage bootstrap/cache 2>/dev/null || true

echo "Deploy Laravel selesai."
