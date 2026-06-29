<?php

namespace App\Events;

use App\Models\AlarmSlot;
use App\Models\Door;
use App\Models\Music;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DoorNotOpenedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Door $door,
        public readonly AlarmSlot $slot,
        public readonly Music $music,
    ) {}
}
