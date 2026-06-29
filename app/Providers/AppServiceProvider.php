<?php

namespace App\Providers;

use App\Events\DisableAlarmEvent;
use App\Events\DoorInteractedEvent;
use App\Events\DoorNotOpenedEvent;
use App\Events\WakeupConfirmedEvent;
use App\Listeners\HandleDisableAlarm;
use App\Listeners\HandleDoorInteracted;
use App\Listeners\HandleDoorNotOpened;
use App\Listeners\HandleWakeupConfirmed;
use App\Contracts\MusicPlayerInterface;
use App\Services\HostMachineMusicPlayer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MusicPlayerInterface::class, HostMachineMusicPlayer::class);
    }

    public function boot(): void
    {
        Event::listen(DoorInteractedEvent::class, HandleDoorInteracted::class);
        Event::listen(DoorNotOpenedEvent::class, HandleDoorNotOpened::class);
        Event::listen(WakeupConfirmedEvent::class, HandleWakeupConfirmed::class);
        Event::listen(DisableAlarmEvent::class, HandleDisableAlarm::class);
    }
}
