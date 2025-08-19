<?php

namespace App\Livewire\Cek;

use Illuminate\Support\Facades\View;
use Livewire\Component;

class ShowCekData extends Component
{
    public $umumData, $asuransiData, $fisioterapiData, $bpjsData;

    public function render()
    {
        View::share('type', 'cek');
        return view('livewire.cek.show-cek-data')
            ->layout('frontend.layout', [
                'subTitle' => 'Cek Nomor Antrian Norm',
                'description' => 'Cek Nomor Antrian Norm Registrasi Online Rumah Sakit Cahya Kawaluyan',
                'subKeywords' => 'cek nomor antrian, cek antrian, cek antrian norm, cek nomor antrian norm'
            ]);
    }

    public function mount($umumData, $asuransiData, $bpjsData, $fisioterapiData): void
    {
        $this->umumData = $umumData;
        $this->asuransiData = $asuransiData;
        $this->bpjsData = $bpjsData;
        $this->fisioterapiData = $fisioterapiData;
    }
}
