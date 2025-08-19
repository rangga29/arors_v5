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
        <h2>REGISTRASI PASIEN BARU SUNDAY CLINIC</h2>
        <p class="lead fs-5">Form Registrasi Digunakan Untuk Khusus Untuk Pasien Sunday Clinic Yang Belum Memiliki Nomor Rekam Medis</p>
        @if(!$isOpen)
            <div class="alert alert-danger">
                @if($currentHour < env('CLOSE_HOUR', '18') && $currentHour <= env('OPEN_HOUR', '7'))
                    <span class="fs-4">Registrasi Sunday Clinic Belum Dibuka</span>
                @else
                    <span class="fs-4">Registrasi Sunday Clinic Belum Dibuka</span>
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
        @if (!$isInBpjs)
            <form wire:submit.prevent="checkPatient">
                <div class="mb-3">
                    <label for="nik" class="form-label fs-4">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="nik" id="nik" wire:model="nik" placeholder="Nomor Induk Kependudukan (NIK)" maxlength="16" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
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
                <button type="submit" class="w-100 btn btn-primary btn-lg" wire:loading.attr="disabled" {{ !$isOpen ? 'disabled' : '' }}>Cek Data</button>
            </form>
        @else
            @livewire('sunday-clinic.s-c-new-appointment', ['patientData' => $patientData])
        @endif
    @endif
</main>
@section('script')
    @vite(['resources/js/customs/patient-check-form.js'])
@endsection
