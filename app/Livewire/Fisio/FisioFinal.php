<?php

namespace App\Livewire\Fisio;

use App\Models\FisioterapiAppointment;
use App\Models\ScheduleDate;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FisioFinal extends Component
{
    public $code, $appointmentData, $scheduleDateData;

    public function render()
    {
        View::share('type', 'fisioterapi');
        return view('livewire.fisio.fisio-final')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Final Pasien Fisioterapi',
                'description' => 'Form Final Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Fisioterapi',
                'subKeywords' => 'form pasien fisioterapi, form pasien'
            ]);
    }

    public function mount($code): void
    {
        $this->code = $code;
        $this->appointmentData = FisioterapiAppointment::where('fap_ucode', $code)->first();
        $this->scheduleDateData = ScheduleDate::where('id', $this->appointmentData['sd_id'])->first();
    }

    public function downloadPdf(): StreamedResponse
    {
        $fileName = Carbon::createFromFormat('Y-m-d', $this->scheduleDateData['sd_date'])->format('Ymd') . '_' . $this->appointmentData['fap_norm'] . '_BuktiRegolFisioRSCK';
        $data = [
            'title' => $fileName,
            'appointmentData' => $this->appointmentData,
            'scheduleDateData' => $this->scheduleDateData
        ];

        $pdf = PDF::loadView('frontend.fisio-print', $data);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }
}
