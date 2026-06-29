<?php

namespace App\Http\Controllers;

use App\Models\Door;
use App\Presenters\SlotStatusPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoorController extends Controller
{
    public function index(): View
    {
        $doors = auth()->user()->doors()->with('alarmSlots.music')->get();

        $slotStatuses = $doors->mapWithKeys(
            fn (Door $door) => [$door->id => (new SlotStatusPresenter($door))->statuses()]
        );

        return view('doors.index', compact('doors', 'slotStatuses'));
    }

    public function create(): View
    {
        return view('doors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'friendly_name' => ['required', 'string', 'max:255'],
            'esp32_device_name' => ['required', 'string', 'max:255', 'unique:doors'],
        ]);

        auth()->user()->doors()->create($validated);

        return redirect()->route('doors.index')->with('success', 'Door created.');
    }

    public function edit(Door $door): View
    {
        abort_if($door->user_id !== auth()->id(), 403);

        $music = auth()->user()->music()->get();
        $door->load('alarmSlots.music');

        $slotStatuses = (new SlotStatusPresenter($door))->statuses();

        return view('doors.edit', compact('door', 'music', 'slotStatuses'));
    }

    public function update(Request $request, Door $door): RedirectResponse
    {
        abort_if($door->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'friendly_name' => ['required', 'string', 'max:255'],
            'esp32_device_name' => ['required', 'string', 'max:255', 'unique:doors,esp32_device_name,'.$door->id],
        ]);

        $door->update($validated);

        return redirect()->route('doors.edit', $door)->with('success', 'Door updated.');
    }

    public function clearWakeup(Door $door): RedirectResponse
    {
        abort_if($door->user_id !== auth()->id(), 403);

        $door->update(['last_interacted_at' => null, 'is_alarm_firing' => false]);

        return redirect()->route('doors.edit', $door)->with('success', 'Wakeup status cleared.');
    }

    public function destroy(Door $door): RedirectResponse
    {
        abort_if($door->user_id !== auth()->id(), 403);

        $door->delete();

        return redirect()->route('doors.index')->with('success', 'Door deleted.');
    }
}
