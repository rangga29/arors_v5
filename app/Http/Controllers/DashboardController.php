<?php

namespace App\Http\Controllers;

use App\Models\AsuransiAppointment;
use App\Models\BpjsKesehatanAppointment;
use App\Models\FisioterapiAppointment;
use App\Models\NewAppointment;
use App\Models\UmumAppointment;
use App\Services\AppointmentDate;
use App\Services\FisioMaxAppointment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected AppointmentDate $appointmentDate;
    protected FisioMaxAppointment $fisioMaxAppointment;

    public function __construct(AppointmentDate $appointmentDate, FisioMaxAppointment $fisioMaxAppointment)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->appointmentDate = $appointmentDate;
        $this->fisioMaxAppointment = $fisioMaxAppointment;
    }

    public function index()
    {
        $dateSelected = $this->appointmentDate->selectNextSevenAppointmentDates();
        $dashboardData = [];

        foreach ($dateSelected as $date) {
            $selectedDateNumber = Carbon::createFromFormat('Y-m-d', $date['sd_date'])->format('N');

            $klinikUmum = UmumAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->whereHas('appointment.scheduleDetail.schedule', function ($query) {
                    $query->whereNotIn('sc_clinic_code', ['KLI013', 'KLI030', 'RHBKAM001']);
                })->count();
            $klinikAsuransi = AsuransiAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->whereHas('appointment.scheduleDetail.schedule', function ($query) {
                    $query->whereNotIn('sc_clinic_code', ['KLI013', 'KLI030', 'RHBKAM001']);
                })->count();
            $klinikBaru = NewAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->whereHas('appointment.scheduleDetail.schedule', function ($query) {
                    $query->whereNotIn('sc_clinic_code', ['KLI013', 'KLI030', 'RHBKAM001']);
                })->count();
            $totalKlinik = $klinikUmum + $klinikAsuransi + $klinikBaru;

            $rehabUmum = UmumAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->whereHas('appointment.scheduleDetail.schedule', function ($query) {
                    $query->whereIn('sc_clinic_code', ['KLI013', 'KLI030', 'RHBKAM001']);
                })->count();
            $rehabAsuransi = AsuransiAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->whereHas('appointment.scheduleDetail.schedule', function ($query) {
                    $query->whereIn('sc_clinic_code', ['KLI013', 'KLI030', 'RHBKAM001']);
                })->count();
            $rehabBaru = NewAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->whereHas('appointment.scheduleDetail.schedule', function ($query) {
                    $query->whereNotIn('sc_clinic_code', ['KLI013', 'KLI030', 'RHBKAM001']);
                })->count();
            $totalRehab = $rehabUmum + $rehabAsuransi + $rehabBaru;

            $fisioPagiNonJkn = FisioterapiAppointment::whereHas('scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->where('fap_type', 'UMUM PAGI')->count();
            $fisioPagiNonJknKuota = $this->fisioMaxAppointment->getMaxPatients($selectedDateNumber, 'UMUM PAGI');
            $fisioSoreNonJkn = FisioterapiAppointment::whereHas('scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->where('fap_type', 'UMUM SORE')->count();
            $fisioSoreNonJknKuota = $this->fisioMaxAppointment->getMaxPatients($selectedDateNumber, 'UMUM SORE');
            $fisioPagiJkn = FisioterapiAppointment::whereHas('scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->where('fap_type', 'BPJS PAGI')->count();
            $fisioPagiJknKuota = $this->fisioMaxAppointment->getMaxPatients($selectedDateNumber, 'BPJS PAGI');
            $fisioSoreJkn = FisioterapiAppointment::whereHas('scheduleDate', function ($query) use ($date) {
                    $query->whereDate('sd_date', $date['sd_date']);
                })->where('fap_type', 'BPJS SORE')->count();
            $fisioSoreJknKuota = $this->fisioMaxAppointment->getMaxPatients($selectedDateNumber, 'BPJS SORE');
            $totalFisio = $fisioPagiNonJkn + $fisioSoreNonJkn + $fisioPagiJkn + $fisioSoreJkn;
            $totalFisioKuota = $fisioPagiNonJknKuota + $fisioSoreNonJknKuota + $fisioPagiJknKuota + $fisioSoreJknKuota;

            $total = $totalKlinik + $totalRehab + $totalFisio;

            $dashboardData[] = [
                'date' => $date['sd_date'],
                'klinikUmum' => $klinikUmum,
                'klinikAsuransi' => $klinikAsuransi,
                'klinikBaru' => $klinikBaru,
                'totalKlinik' => $totalKlinik,
                'rehabUmum' => $rehabUmum,
                'rehabAsuransi' => $rehabAsuransi,
                'rehabBaru' => $rehabBaru,
                'totalRehab' => $totalRehab,
                'fisioPagiNonJkn' => $fisioPagiNonJkn,
                'fisioPagiNonJknKuota' => $fisioPagiNonJknKuota,
                'fisioSoreNonJkn' => $fisioSoreNonJkn,
                'fisioSoreNonJknKuota' => $fisioSoreNonJknKuota,
                'fisioPagiJkn' => $fisioPagiJkn,
                'fisioPagiJknKuota' => $fisioPagiJknKuota,
                'fisioSoreJkn' => $fisioSoreJkn,
                'fisioSoreJknKuota' => $fisioSoreJknKuota,
                'totalFisio' => $totalFisio,
                'totalFisioKuota' => $totalFisioKuota,
                'total' => $total,
            ];
        }

        return view('backend.dashboard', [
            'dashboardData' => $dashboardData,
        ]);
    }
}
