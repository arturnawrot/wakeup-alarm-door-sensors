<?php

namespace App\Http\Controllers;

use App\Models\AlarmSlot;
use App\Models\Door;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlarmSlotController extends Controller
{
    public function store(Request $request, Door $door): RedirectResponse
    {
        abort_if($door->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'music_id' => ['required', 'exists:music,id'],
        ]);

        $door->alarmSlots()->create($validated);

        return redirect()->route('doors.edit', $door)->with('success', 'Alarm slot added.');
    }

    public function update(Request $request, AlarmSlot $alarmSlot): RedirectResponse
    {
        abort_if($alarmSlot->door->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'days'       => ['required', 'array', 'min:1'],
            'days.*'     => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'music_id'   => ['required', 'exists:music,id'],
            'is_active'  => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $alarmSlot->update($validated);

        return redirect()->route('doors.edit', $alarmSlot->door)->with('success', 'Alarm slot updated.');
    }

    public function destroy(AlarmSlot $alarmSlot): RedirectResponse
    {
        abort_if($alarmSlot->door->user_id !== auth()->id(), 403);

        $door = $alarmSlot->door;
        $alarmSlot->delete();

        return redirect()->route('doors.edit', $door)->with('success', 'Alarm slot removed.');
    }
}
