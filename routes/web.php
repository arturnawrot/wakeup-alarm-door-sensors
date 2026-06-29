<?php

use App\Http\Controllers\AlarmSlotController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\DoorController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('doors', DoorController::class);
    Route::post('doors/{door}/clear-wakeup', [DoorController::class, 'clearWakeup'])->name('doors.clear-wakeup');
    Route::post('doors/{door}/alarm-slots', [AlarmSlotController::class, 'store'])->name('alarm-slots.store');
    Route::patch('alarm-slots/{alarmSlot}', [AlarmSlotController::class, 'update'])->name('alarm-slots.update');
    Route::delete('alarm-slots/{alarmSlot}', [AlarmSlotController::class, 'destroy'])->name('alarm-slots.destroy');

    Route::resource('music', MusicController::class)->only(['index', 'store', 'destroy']);
    Route::resource('api-keys', ApiKeyController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/auth.php';
