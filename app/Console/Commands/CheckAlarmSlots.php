<?php

namespace App\Console\Commands;

use App\Events\DoorNotOpenedEvent;
use App\Models\AlarmSlot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckAlarmSlots extends Command
{
    protected $signature = 'alarm:check';
    protected $description = 'Fire DoorNotOpenedEvent for alarm slots that ended without door interaction';

    public function handle(): void
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        $today = strtolower($now->format('l'));

        AlarmSlot::with(['door', 'music'])
            ->where('is_active', true)
            ->where('end_time', $currentTime)
            ->whereJsonContains('days', $today)
            ->get()
            ->each(function (AlarmSlot $slot) use ($now): void {
                $door = $slot->door;
                $startOfSlot = Carbon::today()->setTimeFromTimeString($slot->start_time);
                $endOfSlot = Carbon::today()->setTimeFromTimeString($slot->end_time);

                $interacted = $door->last_interacted_at
                    && $door->last_interacted_at->between($startOfSlot, $endOfSlot);

                if (! $interacted) {
                    DoorNotOpenedEvent::dispatch($door, $slot, $slot->music);
                }
            });
    }
}
