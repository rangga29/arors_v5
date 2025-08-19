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
        <h2>REGISTRASI PASIEN FISIOTERAPI</h2>
        <p class="lead fs-5">Form Registrasi Dikhususkan Untuk Pasien Fisioterapi</p>
        @if(!$isOpen)
            <div class="alert alert-danger">
                @if($currentHour < env('CLOSE_HOUR', '18') && $currentHour <= env('OPEN_HOUR', '7'))
                    <span class="fs-4">Registrasi Untuk Tanggal {{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointmentDate)->isoFormat('dddd, DD MMMM YYYY')  }} Belum Dibuka</span>
                @else
                    <span class="fs-4">Registrasi Untuk Tanggal {{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointmentDate)->isoFormat('dddd, DD MMMM YYYY')  }} Sudah Ditutup</span>
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
        <div class="alert alert-warning text-center text-dark">
            <span class="fs-5">
                <span class="fw-bolder">PENGUMUMAN : </span><br>
                Pasien Fisioterapi Wajib Membawa <span class="fw-bolder">SURAT PENGANTAR</span> dari Dokter Rehabilitasi Medik.
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
                <label for="selectedDate" class="form-label">Tanggal Berobat</label>
                <select class="form-select form-select-lg" id="selectedDate" wire:model.live="selectedDate" required>
                    <option value="" selected>Pilih Tanggal Berobat</option>
                    @foreach($dates as $date)
                        <option value="{{ $date->sd_ucode }}" {{ $date->sd_is_holiday ? 'disabled' : '' }}>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="service" class="form-label fs-4">Pilihan Layanan</label>
                <select class="form-select form-select-lg" name="service" id="service" wire:model="service" required>
                    <option value="" selected>Pilihan Layanan</option>
                    <option value="UMUM PAGI">Pasien Fisioterapi Umum -- Pagi</option>
                    <option value="UMUM SORE">Pasien Fisioterapi Umum -- Sore</option>
                    <option value="BPJS PAGI">Pasien Fisioterapi BPJS -- Pagi</option>
                    <option value="BPJS SORE">Pasien Fisioterapi BPJS -- Sore</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">No Handphone</label>
                <input type="text" class="form-control form-control-lg shadow border-0" name="phone_number" id="phone_number" wire:model="phone_number" placeholder="No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
            </div>
            <button type="submit" class="w-100 btn btn-primary btn-lg" wire:loading.attr="disabled" {{ !$isOpen ? 'disabled' : '' }}>Submit</button>
        </form>
    @endif
</main>

@section('script')
    @vite(['resources/js/customs/patient-check-form.js'])
@endsection
