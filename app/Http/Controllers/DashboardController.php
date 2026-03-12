<?php

namespace App\Http\Controllers;

use App\Models\AsuransiAppointment;
use App\Models\BpjsKesehatanAppointment;
use App\Models\NewAppointment;
use App\Models\UmumAppointment;
use App\Services\AppointmentDate;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected AppointmentDate $appointmentDate;

    public function __construct(AppointmentDate $appointmentDate)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->appointmentDate = $appointmentDate;
    }

    public function index()
    {
        $dateSelected = $this->appointmentDate->selectNextSevenAppointmentDates();

        $rehabCodes = ['KLI013', 'KLI030', 'RHBKAM001']; // These are CLINIC codes
        $fisioDoctorCodes = ['FIS001', 'FIS002'];     // These are DOCTOR codes
        $twDoctorCodes = ['FIS003'];
        $toDoctorCodes = ['FIS004'];
        // Diagnostic clinics exclusion list to separate Rawat Jalan from these specializations.
        // We will exclude the clinics directly (RehabCodes). We also should exclude patients who see these specific doctors (Fisio, TW, TO).
        // Since Fisio/TW/TO belong to Rehab clinic or Diagnostic clinic, excluding RehabCodes from Rawat Jalan usually is enough.

        $dashboardData = [];

        foreach ($dateSelected as $date) {
            $sdDate = $date['sd_date'];
            $isSunday = Carbon::createFromFormat('Y-m-d', $sdDate)->isSunday();

            // === RAWAT JALAN (OUTPATIENT, exclude all diagnostic clinics) ===
            $rjUmum = UmumAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                // Exclude Rehab clinics from generic Rawat Jalan
                $q->whereNotIn('sc_clinic_code', $rehabCodes);
            })->count();

            $rjAsuransi = AsuransiAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                $q->whereNotIn('sc_clinic_code', $rehabCodes);
            })->count();

            $rjBaruUmum = NewAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                $q->whereNotIn('sc_clinic_code', $rehabCodes);
            })->whereNull('nap_business_partner_code')->count();

            $rjBaruAsuransi = NewAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                $q->whereNotIn('sc_clinic_code', $rehabCodes);
            })->whereNotNull('nap_business_partner_code')->count();

            // === KLINIK REHABILITASI MEDIK ===
            $rmUmum = UmumAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                $q->whereIn('sc_clinic_code', $rehabCodes);
            })->count();

            $rmAsuransi = AsuransiAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                $q->whereIn('sc_clinic_code', $rehabCodes);
            })->count();

            $rmBpjs = BpjsKesehatanAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                $q->whereIn('sc_clinic_code', $rehabCodes);
            })->count();

            $rmBaruUmum = NewAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                $q->whereIn('sc_clinic_code', $rehabCodes);
            })->whereNull('nap_business_partner_code')->count();

            $rmBaruAsuransi = NewAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($rehabCodes) {
                $q->whereIn('sc_clinic_code', $rehabCodes);
            })->whereNotNull('nap_business_partner_code')->count();

            // === FISIOTERAPI ===
            $fisioUmum = UmumAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($fisioDoctorCodes) {
                // FISIO uses sc_doctor_code
                $q->whereIn('sc_doctor_code', $fisioDoctorCodes);
            })->count();

            $fisioAsuransi = AsuransiAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($fisioDoctorCodes) {
                $q->whereIn('sc_doctor_code', $fisioDoctorCodes);
            })->count();

            $fisioBpjs = BpjsKesehatanAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($fisioDoctorCodes) {
                $q->whereIn('sc_doctor_code', $fisioDoctorCodes);
            })->count();

            // === TERAPI WICARA ===
            $twUmum = UmumAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($twDoctorCodes) {
                $q->whereIn('sc_doctor_code', $twDoctorCodes);
            })->count();

            $twAsuransi = AsuransiAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($twDoctorCodes) {
                $q->whereIn('sc_doctor_code', $twDoctorCodes);
            })->count();

            $twBpjs = BpjsKesehatanAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($twDoctorCodes) {
                $q->whereIn('sc_doctor_code', $twDoctorCodes);
            })->count();

            // === TERAPI OKUPASI ===
            $toUmum = UmumAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($toDoctorCodes) {
                $q->whereIn('sc_doctor_code', $toDoctorCodes);
            })->count();

            $toAsuransi = AsuransiAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($toDoctorCodes) {
                $q->whereIn('sc_doctor_code', $toDoctorCodes);
            })->count();

            $toBpjs = BpjsKesehatanAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                $q->whereDate('sd_date', $sdDate);
            })->whereHas('appointment.scheduleDetail.schedule', function ($q) use ($toDoctorCodes) {
                $q->whereIn('sc_doctor_code', $toDoctorCodes);
            })->count();

            // === SUNDAY CLINIC (all clinics, no exclusions) ===
            $scUmum = 0;
            $scAsuransi = 0;
            $scBaruUmum = 0;
            $scBaruAsuransi = 0;
            if ($isSunday) {
                $scUmum = UmumAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                    $q->whereDate('sd_date', $sdDate);
                })->count();

                $scAsuransi = AsuransiAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                    $q->whereDate('sd_date', $sdDate);
                })->count();

                $scBaruUmum = NewAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                    $q->whereDate('sd_date', $sdDate);
                })->whereNull('nap_business_partner_code')->count();

                $scBaruAsuransi = NewAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($q) use ($sdDate) {
                    $q->whereDate('sd_date', $sdDate);
                })->whereNotNull('nap_business_partner_code')->count();
            }

            // === TOTAL ===
            $total = $rjUmum + $rjAsuransi + $rjBaruUmum + $rjBaruAsuransi
                + $rmUmum + $rmAsuransi + $rmBpjs + $rmBaruUmum + $rmBaruAsuransi
                + $fisioUmum + $fisioAsuransi + $fisioBpjs
                + $twUmum + $twAsuransi + $twBpjs
                + $toUmum + $toAsuransi + $toBpjs;

            if ($isSunday) {
                $total = $scUmum + $scAsuransi + $scBaruUmum + $scBaruAsuransi;
            }

            $dashboardData[] = [
                'date' => $sdDate,
                'isSunday' => $isSunday,
                // Rawat Jalan
                'rjUmum' => $rjUmum,
                'rjAsuransi' => $rjAsuransi,
                'rjBaruUmum' => $rjBaruUmum,
                'rjBaruAsuransi' => $rjBaruAsuransi,
                // Rehab Medik
                'rmUmum' => $rmUmum,
                'rmAsuransi' => $rmAsuransi,
                'rmBpjs' => $rmBpjs,
                'rmBaruUmum' => $rmBaruUmum,
                'rmBaruAsuransi' => $rmBaruAsuransi,
                // Fisioterapi
                'fisioUmum' => $fisioUmum,
                'fisioAsuransi' => $fisioAsuransi,
                'fisioBpjs' => $fisioBpjs,
                // Terapi Wicara
                'twUmum' => $twUmum,
                'twAsuransi' => $twAsuransi,
                'twBpjs' => $twBpjs,
                // Terapi Okupasi
                'toUmum' => $toUmum,
                'toAsuransi' => $toAsuransi,
                'toBpjs' => $toBpjs,
                // Sunday Clinic
                'scUmum' => $scUmum,
                'scAsuransi' => $scAsuransi,
                'scBaruUmum' => $scBaruUmum,
                'scBaruAsuransi' => $scBaruAsuransi,
                // Total
                'total' => $total,
            ];
        }

        return view('backend.dashboard', [
            'dashboardData' => $dashboardData,
        ]);
    }
}
