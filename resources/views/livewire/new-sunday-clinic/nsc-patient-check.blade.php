@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css'])
@endsection

<main class="background">
    <div wire:loading wire:target="checkPatient" id="overlay-form" style="display: none;">
        <div class="d-flex justify-content-center spinner-container">
            <div class="spinner-border" role="status"></div>
        </div>
    </div>

    <div class="reg-card">
        <div class="reg-header">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan">
            </a>
            <h2>REGISTRASI PASIEN BARU SUNDAY CLINIC</h2>
            <span class="reg-badge">Form Registrasi</span>
        </div>

        <p class="reg-description">
            Form Registrasi Digunakan Untuk Khusus Untuk Pasien Sunday Clinic Yang Belum Memiliki Nomor
            Rekam Medis
        </p>

        @if (!$isOpen)
            <div class="reg-announcement reg-announcement-warning">
                <div class="reg-announcement-content">
                    @if ($currentHour < env('CLOSE_HOUR', '18') && $currentHour <= env('OPEN_HOUR', '7'))
                        Registrasi Sunday Clinic <strong>Belum Dibuka</strong>
                    @else
                        Registrasi Sunday Clinic <strong>Belum Dibuka</strong>
                    @endif
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="reg-announcement">
                <div class="reg-announcement-content">{{ session('error') }}</div>
            </div>
        @endif

        @if ($isOpen)
            @if (!$isInBpjs)
                <form wire:submit.prevent="checkPatient">
                    <div class="reg-section-title">
                        <i class="ri-file-list-3-line"></i> Data Pasien
                    </div>

                    <div class="reg-form-group">
                        <label for="nik" class="reg-label">Nomor Induk Kependudukan (NIK)</label>
                        <input type="text" class="reg-input" name="nik" id="nik" wire:model="nik"
                            placeholder="Masukkan NIK" maxlength="16"
                            oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                    </div>
                    <div class="reg-form-group">
                        <label for="birthday" class="reg-label">Tanggal Lahir</label>
                        <div x-data="{ birthday: '' }">
                            <input type="text" class="reg-input" name="birthday" id="birthday" wire:model="birthday"
                                x-model="birthday" x-mask="99/99/9999" placeholder="dd/mm/yyyy" autocomplete="on"
                                required>
                        </div>
                    </div>
                    <div class="reg-form-group">
                        <label for="service" class="reg-label">Pilihan Layanan</label>
                        <select class="reg-select" name="service" id="service" wire:model="service" required>
                            <option value="" selected>Pilih Layanan</option>
                            <option value="umum">Pasien Umum</option>
                            <option value="asuransi">Pasien Asuransi / Kontraktor</option>
                        </select>
                    </div>

                    <button type="submit" class="reg-submit-btn" wire:loading.attr="disabled"
                        {{ !$isOpen ? 'disabled' : '' }}>
                        <i class="ri-search-line"></i> Cek Data
                    </button>
                </form>
            @else
                @livewire('sunday-clinic.s-c-new-appointment', ['patientData' => $patientData, 'serviceType' => $serviceType])
            @endif
        @endif
    </div>
</main>
@section('script')
    @vite(['resources/js/customs/patient-check-form.js'])
@endsection
