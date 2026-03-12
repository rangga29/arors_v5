<?php

namespace App\Livewire\Fisio;

use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\BpjsKesehatanAppointment;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FisioV2BpjsFinal extends Component
{
    public $code, $appointmentData, $appointmentDetailData, $scheduleDetailData, $scheduleData, $scheduleDateData;

    public function render()
    {
        View::share('type', 'fisioterapi');
        return view('livewire.fisio.fisio-v2-bpjs-final')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Final Pasien Fisioterapi BPJS',
                'description' => 'Form Final Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Fisioterapi BPJS',
                'subKeywords' => 'form pasien fisioterapi bpjs, form pasien'
            ]);
    }

    public function mount($code): void
    {
        $this->code = $code;
        $this->appointmentData = \App\Models\Appointment::where('ap_ucode', $code)->first();
        $this->appointmentDetailData = BpjsKesehatanAppointment::where('ap_id', $this->appointmentData['id'])->first();
        $this->scheduleDetailData = ScheduleDetail::where('id', $this->appointmentData['scd_id'])->first();
        $this->scheduleData = Schedule::where('id', $this->scheduleDetailData['sc_id'])->first();
        $this->scheduleDateData = ScheduleDate::where('id', $this->scheduleData['sd_id'])->first();
    }

    public function downloadPdf(): StreamedResponse
    {
        $fileName = Carbon::createFromFormat('Y-m-d', $this->scheduleDateData['sd_date'])->format('Ymd') . '_' . $this->appointmentDetailData['bap_norm'] . '_BuktiRegolFisioBpjsRSCK';
        $data = [
            'title' => $fileName,
            'appointmentData' => $this->appointmentData,
            'appointmentDetailData' => $this->appointmentDetailData,
            'scheduleDetailData' => $this->scheduleDetailData,
            'scheduleData' => $this->scheduleData,
            'scheduleDateData' => $this->scheduleDateData
        ];

        $pdf = PDF::loadView('frontend.fisio-bpjs-print', $data);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }
}
