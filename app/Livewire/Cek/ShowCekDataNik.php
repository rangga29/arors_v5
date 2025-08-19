<?php

namespace App\Livewire\Cek;

use Illuminate\Support\Facades\View;
use Livewire\Component;

class ShowCekDataNik extends Component
{
    public $newData;

    public function render()
    {
        View::share('type', 'cek');
        return view('livewire.cek.show-cek-data-nik')
            ->layout('frontend.layout', [
                'subTitle' => 'Cek Nomor Antrian NIK',
                'description' => 'Cek Nomor Antrian NIK Registrasi Online Rumah Sakit Cahya Kawaluyan',
                'subKeywords' => 'cek nomor antrian, cek antrian, cek antrian nik, cek nomor antrian nik'
            ]);
    }

    public function mount($newData): void
    {
        $this->newData = $newData;
    }
}
