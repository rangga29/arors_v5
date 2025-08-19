<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api_token'])->group(function () {
    Route::get('/appointment/token/{date}/{token}', [ApiController::class, 'getAppointmentByToken'])->name('appointment.token');
    Route::get('/appointment/opa/{date}/{opaCode}', [ApiController::class, 'getAppointmentByOpa'])->name('appointment.opa');
});
