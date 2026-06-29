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

**Software**

- Docker + Docker Compose
- Node.js (for asset compilation inside the container via `run.sh`)

**Hardware (provided by you)**

- One or more ESP32 microcontrollers wired to door sensors and connected to your LAN
- A machine with speakers attached (the host running the music player microservice) — this is what plays the alarm audio

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

### Example sketch

`examples/esp32_sensor.ino` is a ready-to-flash Arduino sketch. Fill in the four constants at the top before uploading:

```cpp
const char* ssid       = "";          // Wi-Fi network name
const char* password   = "";          // Wi-Fi password
const char* apiKey     = "";          // key from the web UI
const char* deviceName = "esp32-front-door";  // matches an alarm slot
```

The server URL is set to `http://192.168.1.66:8888/api/door/signal` — change it to your host's LAN IP.

Default pin wiring:

| Pin | Purpose       | Mode         |
|-----|---------------|--------------|
| 18  | Door sensor   | INPUT_PULLUP |
| 2   | Onboard LED   | OUTPUT       |

The sketch debounces the sensor signal (75 ms), retries Wi-Fi every 5 s, and retries failed POST requests every 3 s.

## Music player microservice

The Python FastAPI service (`HostMachineMusicPlayer`) must be running on the host machine. It exposes:

| Method | Path      | Body              | Description          |
|--------|-----------|-------------------|----------------------|
| POST   | `/play`   | `{"url": "..."}` | Play a track on loop |
| POST   | `/stop`   |                   | Stop playback        |
| GET    | `/status` |                   | Playback status      |

Playback auto-stops after 60 minutes if `/stop` is not called.

### Example server

`examples/music_player_server.py` is a reference FastAPI implementation that downloads an MP3 from the URL and plays it on loop through the OS default audio device using `pygame-ce`. It is an example — you are expected to implement your own microservice that satisfies the `MusicPlayerInterface` contract (`play(url)` / `stop()`).

**Requirements** (Python 3.14, Windows 10):

```
fastapi
uvicorn[standard]
pygame-ce
```

**Setup:**

```powershell
py -3.14 -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

**Run:**

```powershell
uvicorn main:app --host 0.0.0.0 --port 8000
```

Make sure `MUSIC_PLAYER_URL` in `.env` points to this machine (e.g. `http://192.168.1.x:8000`).

## Queue / Horizon

Visit `/horizon` (authenticated) to monitor jobs and queues.

To pause/resume workers:

```bash
./run.sh horizon_pause
./run.sh horizon_continue
```

## LAN access (WSL2)

With `networkingMode=mirrored` in `.wslconfig` the app is reachable at the Windows host's LAN IP on port `8888`. Add a Windows Firewall inbound rule for TCP `8888` if other devices cannot connect.
