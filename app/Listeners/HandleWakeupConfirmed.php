<?php

namespace App\Listeners;

use App\Events\DisableAlarmEvent;
use App\Events\WakeupConfirmedEvent;

class HandleWakeupConfirmed
{
    public function handle(WakeupConfirmedEvent $event): void
    {
        DisableAlarmEvent::dispatch($event->door);
    }
}
