<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CekBpjsController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataMigrationController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCarolusController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ScheduleBackupController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleDateController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Version2\OldAsuransiController;
use App\Http\Controllers\Version2\OldBaruController;
use App\Http\Controllers\Version2\OldBPJSController;
use App\Http\Controllers\Version2\OldFisioterapiController;
use App\Http\Controllers\Version2\OldUmumController;
use App\Livewire\Baru\BaruFinal;
use App\Livewire\Baru\BaruPatientCheck;
use App\Livewire\Bpjs\BpjsFinal;
use App\Livewire\Bpjs\BpjsPatientCheck;
use App\Livewire\Cek\CekNik;
use App\Livewire\Cek\CekNorm;
use App\Livewire\Batal\BatalNik;
use App\Livewire\Batal\BatalNorm;
use App\Livewire\Fisio\FisioAppointment;
use App\Livewire\Fisio\FisioFinal;
use App\Livewire\SundayClinic\SCNewFinal;
use App\Livewire\SundayClinic\SCOldFinal;
use App\Livewire\SundayClinic\SCOldPatientCheck;
use App\Livewire\SundayClinic\SCNewPatientCheck;
use App\Livewire\Umum\AsuransiFinal;
use App\Livewire\Umum\PatientCheck;
use App\Livewire\Umum\UmumFinal;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.home')->name('home');

Route::get('/registrasi/pasien-umum', PatientCheck::class)->name('umum');
Route::get('/registrasi/pasien-umum/{code}', UmumFinal::class)->name('umum.final');
Route::get('/registrasi/pasien-asuransi/{code}', AsuransiFinal::class)->name('asuransi.final');

Route::get('/registrasi/pasien-bpjs', BpjsPatientCheck::class)->name('bpjs');
Route::get('/registrasi/pasien-bpjs/{code}', BpjsFinal::class)->name('bpjs.final');

Route::view('/registrasi/pasien-rehab-medik-fisioterapi', 'frontend.rehab-fisio')->name('rehab-medik-fisioterapi');

Route::get('/registrasi/pasien-rehab-medik', \App\Livewire\RehabMedik\RMPatientCheck::class)->name('rehab-medik');
Route::get('/registrasi/pasien-rehab-medik/umum/{code}', \App\Livewire\RehabMedik\RMUmumFinal::class)->name('rehab-medik.umum.final');
Route::get('/registrasi/pasien-rehab-medik/asuransi/{code}', \App\Livewire\RehabMedik\RMAsuransiFinal::class)->name('rehab-medik.asuransi.final');
Route::get('/registrasi/pasien-rehab-medik/bpjs/{code}', \App\Livewire\RehabMedik\RMBpjsFinal::class)->name('rehab-medik.bpjs.final');
Route::get('/registrasi/pasien-rehab-medik/baru/{code}', \App\Livewire\RehabMedik\RMBaruFinal::class)->name('rehab-medik.baru.final');
Route::get('/registrasi/pasien-rehab-medik/fisioterapi/{code}', \App\Livewire\RehabMedik\RMFisioFinal::class)->name('rehab-medik.fisio.final');

Route::get('/registrasi/pasien-fisioterapi', FisioAppointment::class)->name('fisioterapi');
Route::get('/registrasi/pasien-fisioterapi/{code}', FisioFinal::class)->name('fisioterapi.final');

Route::get('/registrasi/pasien-baru', BaruPatientCheck::class)->name('baru');
Route::get('/registrasi/pasien-baru/{code}', BaruFinal::class)->name('baru.final');

Route::view('/registrasi/pasien-sunday-clinic', 'frontend.sunday-clinic')->name('sunday-clinic');
Route::get('/registrasi/pasien-sunday-clinic/old-patient', SCOldPatientCheck::class)->name('sunday-clinic.old-patient');
Route::get('/registrasi/pasien-sunday-clinic/old-patient/{code}', SCOldFinal::class)->name('sunday-clinic.old-patient.final');
Route::get('/registrasi/pasien-sunday-clinic/new-patient', SCNewPatientCheck::class)->name('sunday-clinic.new-patient');
Route::get('/registrasi/pasien-sunday-clinic/new-patient/{code}', SCNewFinal::class)->name('sunday-clinic.new-patient.final');

Route::get('/cek-antrian-pasien/norm', CekNorm::class)->name('cek-antrian.norm');
Route::get('/cek-antrian-pasien/nik', CekNik::class)->name('cek-antrian.nik');

Route::get('/batal-antrian-pasien/norm', BatalNorm::class)->name('batal-antrian.norm');
Route::get('/batal-antrian-pasien/nik', BatalNik::class)->name('batal-antrian.nik');

Route::get('/carolus/{qrc_room}/{qrc_ucode}/menu', [QrCarolusController::class, 'menu'])->name('carolus.menu');
Route::get('/carolus/{qrc_room}/{qrc_ucode}/hospital-information', [QrCarolusController::class, 'hospitalInformation'])->name('carolus.hospital-information');
Route::get('/carolus/{qrc_room}/{qrc_ucode}/billing-information/login', [QrCarolusController::class, 'billingForm'])->name('carolus.billing-information.login');
Route::post('/carolus/{qrc_room}/{qrc_ucode}/billing-information/login', [QrCarolusController::class, 'billingCheck'])->name('carolus.billing-information.check');
Route::get('/carolus/{qrc_room}/{qrc_ucode}/billing-information/logout', [QrCarolusController::class, 'billingLogout'])->name('carolus.billing-information.logout');
Route::get('/carolus/{qrc_room}/{qrc_ucode}/billing-information', [QrCarolusController::class, 'billingInformation'])->name('carolus.billing-information');

//Route::prefix('v2')->group(function () {
//    Route::get('/registrasi/pasien-umum', [OldUmumController::class, 'index'])->name('old-umum');
//    Route::post('/registrasi/pasien-umum/patientCheck', [OldUmumController::class, 'patientCheck'])->name('old-umum.patient-check');
//    Route::get('/registrasi/pasien-umum/{patientTemporary}', [OldUmumController::class, 'appointmentCreate'])->name('old-umum.appointment-create');
//    Route::post('/registrasi/pasien-umum/{patientTemporary}', [OldUmumController::class, 'appointmentStore'])->name('old-umum.appointment-store');
//
//    Route::get('/registrasi/pasien-asuransi', [OldAsuransiController::class, 'index'])->name('old-asuransi');
//    Route::post('/registrasi/pasien-asuransi/patientCheck', [OldAsuransiController::class, 'patientCheck'])->name('old-asuransi.patient-check');
//    Route::get('/registrasi/pasien-asuransi/{patientTemporary}', [OldAsuransiController::class, 'appointmentCreate'])->name('old-asuransi.appointment-create');
//    Route::post('/registrasi/pasien-asuransi/{patientTemporary}', [OldAsuransiController::class, 'appointmentStore'])->name('old-asuransi.appointment-store');
//
//    Route::get('/registrasi/pasien-bpjs', [OldBpjsController::class, 'index'])->name('old-bpjs');
//    Route::post('/registrasi/pasien-bpjs/patientCheck', [OldBpjsController::class, 'patientCheck'])->name('old-bpjs.patient-check');
//    Route::get('/registrasi/pasien-bpjs/{patientTemporary}', [OldBpjsController::class, 'appointmentCreate'])->name('old-bpjs.appointment-create');
//    Route::post('/registrasi/pasien-bpjs/{patientTemporary}', [OldBpjsController::class, 'appointmentStore'])->name('old-bpjs.appointment-store');
//
//    Route::get('/registrasi/pasien-fisioterapi', [OldFisioterapiController::class, 'index'])->name('old-fisioterapi');
//    Route::post('/registrasi/pasien-fisioterapi', [OldFisioterapiController::class, 'appointmentStore'])->name('old-fisioterapi.appointment-store');
//
//    Route::get('/registrasi/pasien-baru', [OldBaruController::class, 'index'])->name('old-baru');
//    Route::post('/registrasi/pasien-baru/patientCheck', [OldBaruController::class, 'patientCheck'])->name('old-baru.patient-check');
//    Route::get('/registrasi/pasien-baru/{patientTemporary}', [OldBaruController::class, 'appointmentCreate'])->name('old-baru.appointment-create');
//    Route::post('/registrasi/pasien-baru/{patientTemporary}', [OldBaruController::class, 'appointmentStore'])->name('old-baru.appointment-store');
//});

Route::redirect('/dashboard', '/administrator/dashboard');
Route::prefix('administrator')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [UserController::class, 'login'])->name('login');
        Route::post('/login', [UserController::class, 'authentication'])->name('authentication');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', [RouteController::class, 'index'])->name('root');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('appointments')->group(function () {
            Route::prefix('fisioterapi')->group(function () {
                Route::get('/', function() {
                    return redirect()->to('/administrator/appointments/fisioterapi/'.now()->format('Y-m-d'));
                });
                Route::post('/show', [AppointmentController::class, 'redirectFisio'])->name('appointments.fisioterapi.show.redirect');
                Route::get('/{date}', [AppointmentController::class, 'indexFisio'])->name('appointments.fisioterapi');
            });
            Route::get('/', function() {
                return redirect()->to('/administrator/appointments/'.now()->format('Y-m-d'));
            });
            Route::post('/show', [AppointmentController::class, 'redirectDate'])->name('appointments.show.redirect');
            Route::get('/{date}', [AppointmentController::class, 'index'])->name('appointments');
            Route::get('/{date}/print', [AppointmentController::class, 'printAppointment'])->name('appointments.print');
            Route::post('/show/doctor', [AppointmentController::class, 'redirectDateDoctor'])->name('appointments.doctor.show.redirect');
            Route::get('/{date}/{clinic}/{doctor}/{session}', [AppointmentController::class, 'indexDoctor'])->name('appointments.doctor');
        });

        Route::prefix('schedules')->group(function () {
            Route::prefix('history')->group(function () {
                Route::get('/dates', [ScheduleBackupController::class, 'index'])->name('schedules.backup.dates');
                Route::post('/dates/show', [ScheduleBackupController::class, 'showRedirect'])->name('schedules.backup.dates.show.redirect');
                Route::get('/{date}', [ScheduleBackupController::class, 'view'])->name('schedules.backup');
                Route::get('/{date}/fisio', [ScheduleBackupController::class, 'viewFisioterapi'])->name('schedules.backup.fisio');
                Route::get('/{date}/{clinic}/{doctor}/{session}', [ScheduleBackupController::class, 'viewAppointment'])->name('schedules.backup.appointments');
            });
            Route::prefix('dates')->group(function () {
                Route::get('/', [ScheduleDateController::class, 'index'])->name('schedules.dates');
                Route::post('/', [ScheduleDateController::class, 'store'])->name('schedules.dates.store');
                Route::post('/show', [ScheduleDateController::class, 'showRedirect'])->name('schedules.dates.show.redirect');
                Route::get('/{scheduleDate}/download', [ScheduleDateController::class, 'download'])->name('schedules.dates.download');
                Route::get('/{scheduleDate}/downloadUpdate', [ScheduleDateController::class, 'downloadUpdate'])->name('schedules.dates.downloadUpdate');
                Route::get('/{scheduleDate}', [ScheduleDateController::class, 'show'])->name('schedules.dates.show');
                Route::put('/{scheduleDate}', [ScheduleDateController::class, 'update'])->name('schedules.dates.update');
                Route::delete('/{scheduleDate}', [ScheduleDateController::class, 'destroy'])->name('schedules.dates.destroy');
            });
            Route::get('/', function() {
                return redirect()->to('/administrator/schedules/'.now()->format('Y-m-d'));
            });
            Route::get('/{date}', [ScheduleController::class, 'index'])->name('schedules');
            Route::post('/{date}/available/{schedule}', [ScheduleController::class, 'available'])->name('schedule.available');
            Route::get('/{date}/update/{schedule}/{session}', [ScheduleController::class, 'update'])->name('schedule.update');
            Route::get('/{date}/print', [ScheduleController::class, 'printSchedule'])->name('schedule.print');
            Route::get('/{schedule}/quota', [ScheduleController::class, 'show'])->name('schedule.show');
            Route::put('/{date}/{schedule}/quota', [ScheduleController::class, 'updateQuota'])->name('schedule.quota.update');
        });

        Route::prefix('clinics')->group(function () {
            Route::get('/', [ClinicController::class, 'index'])->name('clinics');
            Route::post('/store', [ClinicController::class, 'store'])->name('clinics.store');
            Route::get('/lastOrder', [ClinicController::class, 'getLastOrder'])->name('clinics.get-last-order');
            Route::get('/{clinic}', [ClinicController::class, 'show'])->name('clinics.show');
            Route::put('/{clinic}', [ClinicController::class, 'update'])->name('clinics.update');
            Route::delete('/{clinic}', [ClinicController::class, 'destroy'])->name('clinics.destroy');
        });

        Route::prefix('users')->group(function () {
            Route::prefix('profile')->group(function () {
                Route::get('/{user}', [ProfileController::class, 'index'])->name('users.profile');
                Route::put('/{user}', [ProfileController::class, 'update'])->name('users.profile.update');
            });
            Route::get('/', [UserController::class, 'index'])->name('users');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::get('/{user}/getRole', [UserController::class, 'getRoleByUser'])->name('users.get-role');
        });

        Route::prefix('logs')->group(function () {
            Route::get('/', [LogController::class, 'index'])->name('logs');
            Route::get('/{user}', [LogController::class, 'getByUser'])->name('logs.user');
        });

        Route::prefix('qrcarolus')->group(function () {
            Route::get('/', [QrCarolusController::class, 'index'])->name('qrcarolus');
            Route::post('/store', [QrCarolusController::class, 'store'])->name('qrcarolus.store');
            Route::get('/{qrcarolus}', [QrCarolusController::class, 'show'])->name('qrcarolus.show');
            Route::put('/{qrcarolus}', [QrCarolusController::class, 'update'])->name('qrcarolus.update');
            Route::delete('/{qrcarolus}', [QrCarolusController::class, 'destroy'])->name('qrcarolus.destroy');
            Route::get('/{qrcarolus}/getQrCode', [QrCarolusController::class, 'getQrCode'])->name('qrcarolus.getqrcode');
        });

        Route::prefix('cek-bpjs')->group(function () {
            Route::get('/sep', [CekBpjsController::class, 'viewCekSep'])->name('cek-bpjs.view-cek-sep');
            Route::post('/sep/cek', [CekBpjsController::class, 'cekSep'])->name('cek-bpjs.cek-sep');
            Route::get('/rujukan', [CekBpjsController::class, 'viewCekRujukan'])->name('cek-bpjs.view-cek-rujukan');
            Route::post('/rujukan/cek', [CekBpjsController::class, 'cekRujukan'])->name('cek-bpjs.cek-rujukan');
        });

        Route::prefix('data-migration')->group(function () {
            Route::get('/', [DataMigrationController::class, 'index'])->name('data-migration');
            Route::post('/', [DataMigrationController::class, 'dataMigration'])->name('data-migration.export');
            Route::get('/print-old-sep', [DataMigrationController::class, 'printOldSep'])->name('data-migration.print-old-sep');
            Route::post('/print-old-sep', [DataMigrationController::class, 'getPrintOldSep'])->name('data-migration.get-print-old-sep');
        });

        Route::post('/logout', [UserController::class, 'logout'])->name('logout');

        Route::get('{first}/{second}/{third}', [RouteController::class, 'thirdLevel'])->name('third');
        Route::get('{first}/{second}', [RouteController::class, 'secondLevel'])->name('second');
        Route::get('{any}', [RouteController::class, 'root'])->name('any');
    });
});

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
