<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return redirect()->route('events.index');
});

Route::resource('events', EventController::class)->except(['edit', 'update']);
Route::get('/checkin/{eventId}', [CheckInController::class, 'scan'])->name('checkin.scan');
Route::post('/checkin/{eventId}', [CheckInController::class, 'checkIn'])->name('checkin.checkin');
Route::get('/events/{id}/export', [ExportController::class, 'export'])->name('events.export');
