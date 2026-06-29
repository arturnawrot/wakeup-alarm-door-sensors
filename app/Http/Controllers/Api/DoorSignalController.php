<?php

namespace App\Http\Controllers\Api;

use App\Events\DoorInteractedEvent;
use App\Http\Controllers\Controller;
use App\Models\Door;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoorSignalController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'esp32_device_name' => ['required', 'string'],
        ]);

        $door = Door::where('esp32_device_name', $request->esp32_device_name)->first();

        if (! $door) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        DoorInteractedEvent::dispatch($door);

        return response()->json(['status' => 'ok']);
    }
}
