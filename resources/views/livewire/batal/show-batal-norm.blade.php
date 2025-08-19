<div>
    <hr>
    @if($umumData->count() !== 0)
        <h5 class="fs-4 text-white">UMUM</h5>
        @foreach($umumData as $uap)
            <div class="card card-body bg-black bg-opacity-25">
                <h4 class="card-title text-white">{{ $uap->appointment->scheduleDetail->schedule->sc_clinic_name }}</h4>
                <p class="card-text">{{ $uap->appointment->scheduleDetail->schedule->sc_doctor_name }}</p>
                <p class="card-text text-uppercase">{{ $uap->appointment->ap_no }}</p>
                <p class="card-text text-uppercase">Nama Pasien : {{$uap->uap_name}}</p>
                <form wire:submit.prevent="deletePatient('{{ $uap->appointment->ap_no }}', 'umum')">
                    <button type="submit" class="w-100 btn btn-danger btn-lg" wire:loading.attr="disabled">Batal Nomor Antrian</button>
                </form>
            </div>
        @endforeach
        <hr>
    @endif
    @if($asuransiData->count() !== 0)
        <h5 class="fs-4 text-white">ASURANSI</h5>
        @foreach($asuransiData as $aap)
            <div class="card card-body bg-black bg-opacity-25">
                <h4 class="card-title text-white">{{ $aap->appointment->scheduleDetail->schedule->sc_clinic_name }}</h4>
                <p class="card-text">{{ $aap->appointment->scheduleDetail->schedule->sc_doctor_name }}</p>
                <p class="card-text text-uppercase">{{ $aap->appointment->ap_no }}</p>
                <p class="card-text text-uppercase">Nama Pasien : {{$aap->aap_name}}</p>
                <form wire:submit.prevent="deletePatient('{{ $aap->appointment->ap_no }}', 'asuransi')">
                    <button type="submit" class="w-100 btn btn-danger btn-lg" wire:loading.attr="disabled">Batal Nomor Antrian</button>
                </form>
            </div>
        @endforeach
        <hr>
    @endif
    @if($bpjsData->count() !== 0)
        <h5 class="fs-4 text-white">BPJS</h5>
        @foreach($bpjsData as $bap)
            <div class="card card-body bg-black bg-opacity-25">
                <h4 class="card-title text-white">{{ $bap->appointment->scheduleDetail->schedule->sc_clinic_name }}</h4>
                <p class="card-text">{{ $bap->appointment->scheduleDetail->schedule->sc_doctor_name }}</p>
                <p class="card-text text-uppercase">{{ $bap->appointment->ap_no }}</p>
                <p class="card-text text-uppercase">Nama Pasien : {{$bap->bap_name}}</p>
                <form wire:submit.prevent="deletePatient('{{ $bap->appointment->ap_no }}', 'bpjs')">
                    <button type="submit" class="w-100 btn btn-danger btn-lg" wire:loading.attr="disabled">Batal Nomor Antrian</button>
                </form>
            </div>
        @endforeach
        <hr>
    @endif
    @if($fisioterapiData->count() !== 0)
        <h5 class="fs-4 text-white">FISIOTERAPI</h5>
        @foreach($fisioterapiData as $fap)
            <div class="card card-body bg-black bg-opacity-25">
                <h4 class="card-title text-white">
                    {{ $fap->fap_type === 'UMUM PAGI' ? 'FISIOTERAPI UMUM PAGI' :
                        ($fap->fap_type == 'UMUM SORE' ? 'FISIOTERAPI UMUM SORE' :
                        ($fap->fap_type == 'BPJS PAGI' ? 'FISIOTERAPI BPJS PAGI' : 'FISIOTERAPI BPJS SORE')) }}
                </h4>
                <p class="card-text text-uppercase">Nama Pasien : {{$fap->fap_name}}</p>
                <form wire:submit.prevent="deletePatientFisio('{{ $fap->fap_ucode }}')">
                    <button type="submit" class="w-100 btn btn-danger btn-lg" wire:loading.attr="disabled">Batal Nomor Antrian</button>
                </form>
            </div>
        @endforeach
        <hr>
    @endif
    <a href="{{ route('home') }}" class="w-100 btn btn-danger text-uppercase">Kembali Ke Halaman Utama</a>
</div>
