<?php

use App\Http\Controllers\Api\DoorSignalController;
use Illuminate\Support\Facades\Route;

Route::middleware('api_key')->post('/door/signal', DoorSignalController::class);
