<?php

namespace App\Presenters;

use App\Models\AlarmSlot;
use App\Models\Door;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotStatusPresenter
{
    private Carbon $now;
    private string $today;

    public function __construct(private readonly Door $door)
    {
        $this->now   = Carbon::now();
        $this->today = strtolower($this->now->format('l'));
    }

    public function statuses(): Collection
    {
        return $this->door->alarmSlots->mapWithKeys(
            fn (AlarmSlot $slot) => [$slot->id => $this->statusFor($slot)]
        );
    }

    private function statusFor(AlarmSlot $slot): array
    {
        if (! in_array($this->today, $slot->days)) {
            return ['label' => 'Not today', 'class' => 'bg-gray-100 text-gray-400', 'dot' => 'text-gray-400'];
        }

        $start  = Carbon::today()->setTimeFromTimeString($slot->start_time);
        $end    = Carbon::today()->setTimeFromTimeString($slot->end_time);
        $wokeUp = $this->door->last_interacted_at?->between($start, $end) ?? false;

        if ($this->now->lt($start)) {
            return ['label' => 'Upcoming', 'class' => 'bg-blue-100 text-blue-600', 'dot' => 'text-blue-400'];
        }

        if ($this->now->between($start, $end)) {
            return $wokeUp
                ? ['label' => 'Woke up ✓', 'class' => 'bg-green-100 text-green-700', 'dot' => 'text-green-500']
                : ['label' => 'Waiting…',  'class' => 'bg-yellow-100 text-yellow-700', 'dot' => 'text-yellow-500'];
        }

        if ($wokeUp) {
            return ['label' => 'Woke up ✓', 'class' => 'bg-green-100 text-green-700', 'dot' => 'text-green-500'];
        }

        if ($this->door->is_alarm_firing) {
            return ['label' => 'Missed', 'class' => 'bg-red-100 text-red-600', 'dot' => 'text-red-500'];
        }

        return ['label' => 'Not today', 'class' => 'bg-gray-100 text-gray-400', 'dot' => 'text-gray-400'];
    }
}
