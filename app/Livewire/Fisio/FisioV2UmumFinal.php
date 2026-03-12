<?php

namespace App\Livewire\Fisio;

use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\UmumAppointment;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FisioV2UmumFinal extends Component
{
    public $code, $appointmentData, $appointmentDetailData, $scheduleDetailData, $scheduleData, $scheduleDateData;

    public function render()
    {
        View::share('type', 'fisioterapi');
        return view('livewire.fisio.fisio-v2-umum-final')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Final Pasien Fisioterapi Umum',
                'description' => 'Form Final Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Fisioterapi Umum',
                'subKeywords' => 'form pasien fisioterapi umum, form pasien'
            ]);
    }

    public function mount($code): void
    {
        $this->code = $code;
        $this->appointmentData = \App\Models\Appointment::where('ap_ucode', $code)->first();
        $this->appointmentDetailData = UmumAppointment::where('ap_id', $this->appointmentData['id'])->first();
        $this->scheduleDetailData = ScheduleDetail::where('id', $this->appointmentData['scd_id'])->first();
        $this->scheduleData = Schedule::where('id', $this->scheduleDetailData['sc_id'])->first();
        $this->scheduleDateData = ScheduleDate::where('id', $this->scheduleData['sd_id'])->first();
    }

    public function downloadPdf(): StreamedResponse
    {
        $fileName = Carbon::createFromFormat('Y-m-d', $this->scheduleDateData['sd_date'])->format('Ymd') . '_' . $this->appointmentDetailData['uap_norm'] . '_BuktiRegolFisioUmumRSCK';

        // Generate QR Code sebagai PNG base64
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => 10,
            'imageBase64' => false,
        ]);
        $qrCodeImage = (new QRCode($options))->render($this->appointmentData['ap_no']);
        $qrCodeBase64 = base64_encode($qrCodeImage);

        $data = [
            'title' => $fileName,
            'appointmentData' => $this->appointmentData,
            'appointmentDetailData' => $this->appointmentDetailData,
            'scheduleDetailData' => $this->scheduleDetailData,
            'scheduleData' => $this->scheduleData,
            'scheduleDateData' => $this->scheduleDateData,
            'qrCodeBase64' => $qrCodeBase64,
        ];

        $pdf = PDF::loadView('frontend.fisio-umum-print', $data);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }
}
