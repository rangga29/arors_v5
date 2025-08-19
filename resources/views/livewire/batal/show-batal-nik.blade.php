<div>
    @if($newData->count() !== 0)
        <h5 class="fs-4 text-white">UMUM</h5>
        @foreach($newData as $nap)
            <div class="card card-body bg-black bg-opacity-25">
                <h4 class="card-title text-white">{{ $nap->appointment->scheduleDetail->schedule->sc_clinic_name }}</h4>
                <p class="card-text">{{ $nap->appointment->scheduleDetail->schedule->sc_doctor_name }}</p>
                <p class="card-text">{{ $nap->appointment->ap_no }}</p>
                <form wire:submit.prevent="deletePatient('{{ $nap->appointment->ap_no }}')">
                    <button type="submit" class="w-100 btn btn-danger btn-lg" wire:loading.attr="disabled">Batal Nomor Antrian</button>
                </form>
            </div>
        @endforeach
        <hr>
    @endif
    <a href="{{ route('home') }}" class="w-100 btn btn-danger text-uppercase">Kembali Ke Halaman Utama</a>
</div>
