# Wakeup

Door alarm system running on a Raspberry Pi 4. ESP32 sensors report door activity via HTTP. If a door is not interacted with during a configured time slot, an alarm fires and plays music through a connected USB speaker via a companion Python microservice.

## How it works

1. ESP32 sensors send a signal to the API endpoint whenever a door is interacted with.
2. A scheduler runs every minute and checks active alarm slots. If a slot's window has ended and no signal was received, it fires a `DoorNotOpenedEvent`.
3. The listener handles the event and tells the music player microservice to start playing the alarm track.
4. When the door is subsequently interacted with, `WakeupConfirmedEvent` fires and the music stops.

## Stack

- **Laravel 13** (PHP 8.4) — web UI, scheduler, queue
- **Laravel Horizon** — queue dashboard and worker management
- **SQLite** — database
- **Redis** — queue backend
- **Docker Compose** — app, nginx, redis, horizon, scheduler
- **Python FastAPI** — music player microservice running on the host machine

## Services

| Service     | Purpose                                      |
|-------------|----------------------------------------------|
| `app`       | PHP-FPM application                          |
| `nginx`     | Web server on port `8888`                    |
| `redis`     | Queue backend                                |
| `horizon`   | Queue worker (supervisord)                   |
| `scheduler` | Runs `php artisan schedule:work`             |

## Requirements

- Docker + Docker Compose
- Node.js (for asset compilation inside the container via `run.sh`)

## Setup

```bash
docker compose up -d
./run.sh setup
```

`setup` installs Composer and npm dependencies, generates the app key, links storage, builds frontend assets, runs migrations with seed, and sets file permissions.

The default admin account created by the seeder:

| Field    | Value           |
|----------|-----------------|
| Email    | admin@admin.com |
| Password | 1234511         |

## Configuration

Copy `.env.example` to `.env` (or edit `.env` directly) and adjust:

```dotenv
APP_TIMEZONE=America/New_York   # timezone for alarm slot comparisons

MUSIC_PLAYER_URL=http://192.168.11.1:8000  # host machine FastAPI service
```

## run.sh reference

```bash
./run.sh setup              # first-time setup
./run.sh migrate            # run pending migrations
./run.sh reset_db           # fresh migrate + seed
./run.sh artisan <cmd>      # any artisan command
./run.sh composer <cmd>     # composer inside the container
./run.sh npm <cmd>          # npm inside the container
./run.sh dev                # start Vite dev server
./run.sh build              # build frontend assets
./run.sh optimize           # clear and rebuild all caches
./run.sh logs [service]     # tail Docker logs
./run.sh set_file_permissions
```

## ESP32 API

### Signal door interaction

```
POST /api/door/signal
X-API-Key: <key>

{ "esp32_device_name": "front-door" }
```

Returns `200 OK` on success. API keys are managed in the web UI under **API Keys**.

## Music player microservice

The Python FastAPI service (`HostMachineMusicPlayer`) must be running on the host machine. It exposes:

| Method | Path      | Body              | Description          |
|--------|-----------|-------------------|----------------------|
| POST   | `/play`   | `{"url": "..."}` | Play a track on loop |
| POST   | `/stop`   |                   | Stop playback        |
| GET    | `/status` |                   | Playback status      |

Playback auto-stops after 60 minutes if `/stop` is not called.

## Queue / Horizon

Visit `/horizon` (authenticated) to monitor jobs and queues.

To pause/resume workers:

```bash
./run.sh horizon_pause
./run.sh horizon_continue
```

## LAN access (WSL2)

With `networkingMode=mirrored` in `.wslconfig` the app is reachable at the Windows host's LAN IP on port `8888`. Add a Windows Firewall inbound rule for TCP `8888` if other devices cannot connect.
