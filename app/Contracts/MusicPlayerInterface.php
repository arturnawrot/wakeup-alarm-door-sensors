<?php

namespace App\Contracts;

interface MusicPlayerInterface
{
    public function play(string $url): void;

    public function stop(): void;
}
