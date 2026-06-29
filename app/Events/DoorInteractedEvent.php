<?php

namespace App\Events;

use App\Models\Door;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DoorInteractedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Door $door) {}
}
