<?php

namespace App\Livewire\RehabMedik;

use App\Models\NewAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RMBaruFinal extends Component
{
    public $code, $appointmentData, $appointmentDetailData, $scheduleDetailData, $scheduleData, $scheduleDateData;

    public function render()
    {
        View::share('type', 'rehab-medik');
        return view('livewire.rehab-medik.rehab-baru-final')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Final Pasien Rehab Medik Baru',
                'description' => 'Form Final Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Rehab Medik Baru',
                'subKeywords' => 'form pasien rehab medik baru, form pasien'
            ]);
    }

    public function mount($code): void
    {
        $this->code = $code;
        $this->appointmentData = \App\Models\Appointment::where('ap_ucode', $code)->first();
        $this->appointmentDetailData = NewAppointment::where('ap_id', $this->appointmentData['id'])->first();
        $this->scheduleDetailData = ScheduleDetail::where('id', $this->appointmentData['scd_id'])->first();
        $this->scheduleData = Schedule::where('id', $this->scheduleDetailData['sc_id'])->first();
        $this->scheduleDateData = ScheduleDate::where('id', $this->scheduleData['sd_id'])->first();
    }

    public function downloadPdf(): StreamedResponse
    {
        $fileName = Carbon::createFromFormat('Y-m-d', $this->scheduleDateData['sd_date'])->format('Ymd') . '_' . $this->appointmentDetailData['uap_norm'] . '_BuktiRegolPasienRehabMedikBaruRSCK';
        $data = [
            'title' => $fileName,
            'appointmentData' => $this->appointmentData,
            'appointmentDetailData' => $this->appointmentDetailData,
            'scheduleDetailData' => $this->scheduleDetailData,
            'scheduleData' => $this->scheduleData,
            'scheduleDateData' => $this->scheduleDateData,
        ];

        $pdf = PDF::loadView('frontend.rehab-baru-print', $data);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }
}
