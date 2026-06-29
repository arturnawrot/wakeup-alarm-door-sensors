"""FastAPI server that plays an MP3 from a URL on a loop through the OS speakers.

Playback stops automatically after STOP_AFTER_MINUTES (default 60) or when the
/stop endpoint is called.

requirements.txt
fastapi
uvicorn[standard]
pygame-ce

## Setup (Python 3.14, Windows 10)

```powershell
py -3.14 -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
```

## Run

```powershell
uvicorn main:app --host 0.0.0.0 --port 8000
```

It's just an example that might or might not be suitable for your needs. 
You are supposed to implement a microservice that will play music through loudspeakers yourself.
"""

from __future__ import annotations

import os
import tempfile
import threading
import time
from contextlib import asynccontextmanager
from urllib.request import Request, urlopen

import pygame
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, HttpUrl

STOP_AFTER_MINUTES = 60
DOWNLOAD_CHUNK = 64 * 1024


class PlayRequest(BaseModel):
    url: HttpUrl


class Player:
    """Owns the single audio stream and the auto-stop timer.

    pygame.mixer plays on the OS default output device, so audio is routed to
    whatever speakers are currently connected.
    """

    def __init__(self, stop_after_minutes: int = STOP_AFTER_MINUTES) -> None:
        self._stop_after_seconds = stop_after_minutes * 60
        self._lock = threading.Lock()
        self._timer: threading.Timer | None = None
        self._current_file: str | None = None
        self._started_at: float | None = None
        self._source_url: str | None = None

    def _download(self, url: str) -> str:
        """Download the mp3 to a temp file and return its path."""
        req = Request(url, headers={"User-Agent": "music-player/1.0"})
        fd, path = tempfile.mkstemp(suffix=".mp3")
        try:
            with os.fdopen(fd, "wb") as out, urlopen(req, timeout=30) as resp:
                while chunk := resp.read(DOWNLOAD_CHUNK):
                    out.write(chunk)
        except Exception:
            self._safe_remove(path)
            raise
        return path

    @staticmethod
    def _safe_remove(path: str | None) -> None:
        if path and os.path.exists(path):
            try:
                os.remove(path)
            except OSError:
                pass

    def play(self, url: str) -> None:
        with self._lock:
            self._stop_locked()  # replace anything already playing

            path = self._download(url)
            try:
                pygame.mixer.music.load(path)
                pygame.mixer.music.play(loops=-1)  # -1 == loop forever
            except pygame.error as exc:
                self._safe_remove(path)
                raise RuntimeError(f"could not play audio: {exc}") from exc

            self._current_file = path
            self._source_url = url
            self._started_at = time.monotonic()

            self._timer = threading.Timer(self._stop_after_seconds, self.stop)
            self._timer.daemon = True
            self._timer.start()

    def stop(self) -> None:
        with self._lock:
            self._stop_locked()

    def _stop_locked(self) -> None:
        if self._timer is not None:
            self._timer.cancel()
            self._timer = None
        try:
            pygame.mixer.music.stop()
            pygame.mixer.music.unload()
        except pygame.error:
            pass
        self._safe_remove(self._current_file)
        self._current_file = None
        self._source_url = None
        self._started_at = None

    def status(self) -> dict:
        with self._lock:
            playing = pygame.mixer.music.get_busy() and self._started_at is not None
            elapsed = (
                round(time.monotonic() - self._started_at, 1)
                if self._started_at is not None
                else None
            )
            remaining = (
                round(self._stop_after_seconds - (time.monotonic() - self._started_at), 1)
                if self._started_at is not None
                else None
            )
            return {
                "playing": playing,
                "source_url": self._source_url,
                "elapsed_seconds": elapsed,
                "remaining_seconds": remaining,
                "stop_after_minutes": self._stop_after_seconds // 60,
            }


player = Player()


@asynccontextmanager
async def lifespan(app: FastAPI):
    pygame.mixer.init()
    try:
        yield
    finally:
        player.stop()
        pygame.mixer.quit()


app = FastAPI(title="Music Player", lifespan=lifespan)


@app.post("/play")
def play(req: PlayRequest) -> dict:
    try:
        player.play(str(req.url))
    except RuntimeError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    except Exception as exc:  # network / download errors
        raise HTTPException(status_code=502, detail=f"could not fetch url: {exc}") from exc
    return {"message": "playing", **player.status()}


@app.post("/stop")
def stop() -> dict:
    player.stop()
    return {"message": "stopped"}


@app.get("/status")
def status() -> dict:
    return player.status()
