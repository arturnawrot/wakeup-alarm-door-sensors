<?php

namespace App\Services;

use App\Contracts\MusicPlayerInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HostMachineMusicPlayer implements MusicPlayerInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.music_player.url'), '/');
    }

    public function play(string $url): void
    {
        try {
            Http::post("{$this->baseUrl}/play", ['url' => $url])->throw();
        } catch (\Throwable $e) {
            Log::error('MusicPlayer: failed to start playback', ['url' => $url, 'error' => $e->getMessage()]);
        }
    }

    public function stop(): void
    {
        try {
            Http::post("{$this->baseUrl}/stop")->throw();
        } catch (\Throwable $e) {
            Log::error('MusicPlayer: failed to stop playback', ['error' => $e->getMessage()]);
        }
    }
}
