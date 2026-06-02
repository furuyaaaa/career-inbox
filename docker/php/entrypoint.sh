#!/bin/sh
set -e

export COMPOSER_PROCESS_TIMEOUT="${COMPOSER_PROCESS_TIMEOUT:-1200}"

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
  composer install --no-progress
fi

if [ -d storage ] && [ -d bootstrap/cache ]; then
  chown -R www-data:www-data storage bootstrap/cache || true
fi

if [ -f artisan ] && ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --ansi
fi

exec "$@"
