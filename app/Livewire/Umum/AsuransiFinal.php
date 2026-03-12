<?php

namespace App\Livewire\Umum;

use App\Models\AsuransiAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AsuransiFinal extends Component
{
    public $code, $service, $appointmentData, $appointmentDetailData, $scheduleDetailData, $scheduleData, $scheduleDateData;

    public function render()
    {
        View::share('type', 'umum');
        return view('livewire.umum.asuransi-final')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Final Pasien Kontraktor',
                'description' => 'Form Final Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Kontraktor',
                'subKeywords' => 'form pasien asuransi, form pasien, form pasien kontraktor'
            ]);
    }

    public function mount($code): void
    {
        $this->code = $code;
        $this->appointmentData = \App\Models\Appointment::where('ap_ucode', $code)->first();
        $this->appointmentDetailData = AsuransiAppointment::where('ap_id', $this->appointmentData['id'])->first();
        $this->scheduleDetailData = ScheduleDetail::where('id', $this->appointmentData['scd_id'])->first();
        $this->scheduleData = Schedule::where('id', $this->scheduleDetailData['sc_id'])->first();
        $this->scheduleDateData = ScheduleDate::where('id', $this->scheduleData['sd_id'])->first();

        $segments = explode('/', request()->url());
        $desiredSegment = $segments[3] ?? '';
        $this->service = $desiredSegment;
    }

    public function downloadPdf(): StreamedResponse
    {
        $fileName = Carbon::createFromFormat('Y-m-d', $this->scheduleDateData['sd_date'])->format('Ymd') . '_' . $this->appointmentDetailData['uap_norm'] . '_BuktiRegolAsuransiRSCK';
        $data = [
            'title' => $fileName,
            'appointmentData' => $this->appointmentData,
            'appointmentDetailData' => $this->appointmentDetailData,
            'scheduleDetailData' => $this->scheduleDetailData,
            'scheduleData' => $this->scheduleData,
            'scheduleDateData' => $this->scheduleDateData,
            'service' => $this->service
        ];

        $pdf = PDF::loadView('frontend.asuransi-print', $data);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }
}
