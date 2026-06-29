<?php

namespace App\Listeners;

use App\Events\DoorInteractedEvent;
use App\Events\WakeupConfirmedEvent;

class HandleDoorInteracted
{
    public function handle(DoorInteractedEvent $event): void
    {
        $door = $event->door;

        $door->update(['last_interacted_at' => now()]);

        if ($door->is_alarm_firing) {
            WakeupConfirmedEvent::dispatch($door->fresh());
        }
    }
}
