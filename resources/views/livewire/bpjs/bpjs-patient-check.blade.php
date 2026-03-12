@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css'])
@endsection

<main class="background">
    <div wire:loading wire:target="checkPatient" id="overlay-form" style="display: none;">
        <div class="d-flex justify-content-center spinner-container">
            <div class="spinner-border" role="status"></div>
        </div>
    </div>

    <div class="pb-3 text-center">
        <a href="{{ route('home') }}">
            <img class="d-block mx-auto mb-4" src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="" height="57">
        </a>
        <h2>REGISTRASI PASIEN BPJS</h2>
        <p class="lead fs-5">Form Registrasi Digunakan Untuk Pasien BPJS Yang Sudah Memiliki Nomor Rekam Medis (NORM)</p>

        @if(!$isOpen)
            <div class="alert alert-danger">
                @if($currentHour < env('CLOSE_HOUR', '18') && $currentHour <= env('OPEN_HOUR', '7'))
                    <span class="fs-4">Registrasi Untuk Tanggal {{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointmentDate)->isoFormat('dddd, DD MMMM YYYY')  }} Belum Dibuka</span>
                @else
                    <span class="fs-4">Registrasi Untuk Tanggal {{ \Carbon\Carbon::createFromFormat('Y-m-d', $todayDate)->isoFormat('dddd, DD MMMM YYYY')  }} Sudah Ditutup</span>
                @endif
            </div>
        @endif
    </div>

    @if (session()->has('error'))
        <div class="alert alert-danger">
            <span class="fs-4">{{ session('error') }}</span>
        </div>
    @endif

    @if($isOpen)
        @if (!$isInMedin)
            <div class="alert alert-danger text-center">
            <span class="fs-5">
                <span class="fw-bolder">PENGUMUMAN : </span><br>
                Mulai Tanggal <span class="fw-bolder">10 Oktober 2024</span> Pasien BPJS <span class="fw-bolder">WAJIB</span> Menggunakan Aplikasi Mobile JKN.
                Tata Cara Penggunaan Mobile JKN Klik <a href="https://registrasi.rscahyakawaluyan.com/images/tatacara_mobilejkn.jpg" class="fw-bolder" target="_blank">DISINI</a>
            </span>
            </div>
            <form wire:submit.prevent="checkPatient">
                <div class="mb-3">
                    <label for="norm" class="form-label fs-4">No Rekam Medis (NORM)</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="norm" id="norm" wire:model="norm" placeholder="No Medical Record (NORM)" maxlength="8" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                <div class="mb-3">
                    <label for="birthday" class="form-label fs-4">Tanggal Lahir</label>
                    <div x-data="{ birthday: '' }">
                        <input type="text"
                               class="form-control form-control-lg shadow border-0"
                               name="birthday"
                               id="birthday"
                               wire:model="birthday"
                               x-model="birthday"
                               x-mask="99/99/9999"
                               placeholder="dd/mm/yyyy"
                               autocomplete="on"
                               required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="ppk1" class="form-label fs-4">No Rujukan PPK 1</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="ppk1" id="ppk1" wire:model="ppk1" placeholder="No Rujukan PPK 1" maxlength="19" autocomplete required>
                </div>
                <button type="submit" class="w-100 btn btn-primary btn-lg" wire:loading.attr="disabled" {{ !$isOpen ? 'disabled' : '' }}>Cek Data</button>
            </form>
        @else
            @livewire('bpjs.bpjs-appointment', ['patientData' => $patientData, 'bpjsData' => $bpjsData])
        @endif
    @endif
</main>

@section('script')
    @vite(['resources/js/customs/patient-check-form.js'])
@endsection
