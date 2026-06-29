#!/usr/bin/env bash

set -o errexit
set -o pipefail

DC="${DC:-exec}"
TTY=""
if [[ ! -t 1 ]]; then
  TTY="-T"
fi

function _dc {
  docker compose "${DC}" ${TTY} "${@}"
}

function composer {
  _dc app composer "${@}"
}

function npm {
  _dc app npm "${@}"
}

function npm_dev {
  npm run dev
}

function npm_install {
  npm install
}

function npm_build {
  npm run build
}

function dev {
  npm_dev
}

function build {
  npm_build
}

function artisan {
  _dc app php artisan "${@}"
}

function migrate {
  artisan migrate
}

function horizon {
  artisan horizon
}

function horizon_terminate {
  artisan horizon:terminate
}

function horizon_pause {
  artisan horizon:pause
}

function horizon_continue {
  artisan horizon:continue
}

function scheduler_logs {
  docker compose logs -f scheduler
}

function test {
  flush_redis
  artisan config:clear
  artisan route:clear
  artisan view:clear
  artisan test "${@}"
}

function reset_db {
  flush_redis
  artisan migrate:fresh --seed
}

function flush_redis {
  _dc redis redis-cli FLUSHALL
}

function logs {
  docker compose logs -f "${@}"
}

function set_file_permissions {
  _dc app chown -R laravel:www-data .
  _dc app chmod -R 775 storage/ bootstrap/cache/ database/
}

function optimize {
  artisan cache:clear
  artisan config:clear
  artisan route:clear
  artisan view:clear
  artisan config:cache
  artisan route:cache
  artisan view:cache
}

function fetch_updates {
  git pull
  docker compose pull
  docker compose up -d
  migrate
  optimize
  set_file_permissions
}

function setup {
  composer install
  npm_install
  artisan key:generate
  artisan storage:link
  npm_build
  reset_db
  set_file_permissions
}

function help {
  printf "%s <task> [args]\n\nTasks:\n" "${0}"
  compgen -A function | grep -v "^_" | cat -n
}

TIMEFORMAT=$'\nTask completed in %3lR'
time "${@:-help}"
