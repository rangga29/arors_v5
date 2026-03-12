<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ExternalApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api_token'])->group(function () {
    Route::get('/appointment/token/{date}/{token}', [ApiController::class, 'getAppointmentByToken'])->name('appointment.token');
    Route::get('/appointment/opa/{date}/{opaCode}', [ApiController::class, 'getAppointmentByOpa'])->name('appointment.opa');
});

Route::middleware(['external_api_bearer'])->prefix('external')->group(function () {
    Route::post('/appointment/check', [ExternalApiController::class, 'checkAppointment'])->name('external.appointment.check');
    Route::post('/appointment/register', [ExternalApiController::class, 'registerAppointment'])->name('external.appointment.register');
});
