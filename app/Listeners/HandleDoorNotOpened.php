<?php

namespace App\Listeners;

use App\Contracts\MusicPlayerInterface;
use App\Events\DoorNotOpenedEvent;

class HandleDoorNotOpened
{
    public function __construct(private readonly MusicPlayerInterface $player) {}

    public function handle(DoorNotOpenedEvent $event): void
    {
        $event->door->update(['is_alarm_firing' => true]);

        $this->player->play($event->music->url);
    }
}
