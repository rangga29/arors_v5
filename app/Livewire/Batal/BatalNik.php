<?php

namespace App\Livewire\Batal;

use App\Models\NewAppointment;
use App\Services\AppointmentDate;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class BatalNik extends Component
{
    public $nik, $birthday, $selectedDate, $isHaveData, $appointmentDates;
    public $isHaveAppointment, $umumData, $asuransiData, $fisioterapiData, $bpjsData, $newData;

    protected AppointmentDate $appointmentDate;

    public function boot(AppointmentDate $appointmentDate): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->appointmentDate = $appointmentDate;
    }

    public function render()
    {
        View::share('type', 'batal');
        return view('livewire.batal.batal-nik', [
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
        ])->layout('frontend.layout', [
            'subTitle' => 'Pembatalan Nomor Antrian NIK',
            'description' => 'Pembatalan Nomor Antrian NIK Registrasi Online Rumah Sakit Cahya Kawaluyan',
            'subKeywords' => 'pembatalan nomor antrian, pembatalan antrian, pembatalan antrian nik, pembatalan nomor antrian nik'
        ]);
    }

    public function mount(): void
    {
        $this->appointmentDates = $this->appointmentDate->selectNextSevenAppointmentDates();
    }

    public function checkPatient()
    {
        $date = $this->selectedDate;

        try {
            $birthdateDb = Carbon::createFromFormat('d/m/Y', $this->birthday)->format('Y-m-d');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah');
        }

        $newAppointmentExists = NewAppointment::with('appointment.scheduleDetail.schedule.scheduleDate')
            ->where('nap_ssn', $this->nik)
            ->whereDate('nap_birthday', $birthdateDb)
            ->whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($date) {
                $query->where('sd_date', $date);
            })->get();

        if ($newAppointmentExists->count() === 0) {
            return back()->with('error', 'Data Pasien Tidak Ditemukan. Silahkan Cek Kembali Nomor Rekam Medis / NIK Pasien.');
        } else {
            $this->isHaveAppointment = true;
            $this->newData = $newAppointmentExists;
        }
    }
}
