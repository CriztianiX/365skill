<?php

use App\Http\Controllers\Api\ReservationsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/reservations', ReservationsController::class .'@index')->name('reservations.index');
Route::post('/api/reservations', ReservationsController::class .'@store')->name('reservations.store');
Route::put('/api/reservations/{id}/status', ReservationsController::class .'@updateStatus')->name('reservations.updateStatus');
