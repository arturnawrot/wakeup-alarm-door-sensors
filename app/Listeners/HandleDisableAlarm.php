<?php

namespace App\Listeners;

use App\Contracts\MusicPlayerInterface;
use App\Events\DisableAlarmEvent;

class HandleDisableAlarm
{
    public function __construct(private readonly MusicPlayerInterface $player) {}

    public function handle(DisableAlarmEvent $event): void
    {
        $event->door->update(['is_alarm_firing' => false]);

        $this->player->stop();
    }
}
