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
            <h2>REGISTRASI PASIEN UMUM / ASURANSI / KONTRAKTOR</h2>
            <span class="reg-badge">Form Registrasi</span>
        </div>

        <p class="reg-description">
            Form Registrasi Digunakan Untuk Pasien Umum / Asuransi / Kontraktor Yang Sudah Memiliki
            Nomor Rekam Medis (NORM)
        </p>

        @if (!$isOpen)
            <div class="reg-announcement reg-announcement-warning">
                <div class="reg-announcement-content">
                    @if ($currentHour < env('CLOSE_HOUR', '18') && $currentHour <= env('OPEN_HOUR', '7'))
                        Registrasi Untuk Tanggal
                        <strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointmentDate)->isoFormat('dddd, DD MMMM YYYY') }}</strong>
                        Belum Dibuka
                    @else
                        Registrasi Untuk Tanggal
                        <strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointmentDate)->isoFormat('dddd, DD MMMM YYYY') }}</strong>
                        Sudah Ditutup
                    @endif
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="reg-announcement">
                <div class="reg-announcement-content">{{ session('error') }}</div>
            </div>
        @endif

        @if (session()->has('error1'))
            <div class="reg-announcement">
                <div class="reg-announcement-content">{{ session('error1') }}</div>
            </div>
        @endif

        @if (session()->has('error2'))
            <div class="reg-announcement">
                <div class="reg-announcement-content">
                    {{ session('error2') }} | Bukti Pendaftaran Dapat Klik
                    @if (session('serviceType') == 'umum')
                        <a href="{{ route('umum.final', session('error3')) }}"
                            style="color: #6ea8fe; font-weight: 600;">DISINI</a>
                    @else
                        <a href="{{ route('asuransi.final', session('error3')) }}"
                            style="color: #6ea8fe; font-weight: 600;">DISINI</a>
                    @endif
                </div>
            </div>
        @endif

        @if ($isOpen)
            @if (!$isInMedin)
                <form wire:submit.prevent="checkPatient">
                    <div class="reg-section-title">
                        <i class="ri-file-list-3-line"></i> Data Pasien
                    </div>

                    <div class="reg-form-group">
                        <label for="norm" class="reg-label">No Rekam Medis (NORM)</label>
                        <input type="text" class="reg-input" name="norm" id="norm" wire:model="norm"
                            placeholder="Masukkan No Rekam Medis" maxlength="8"
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
                @livewire('umum.appointment', ['patientData' => $patientData, 'serviceType' => $serviceType])
            @endif
        @endif
    </div>
</main>

@section('script')
    @vite(['resources/js/customs/patient-check-form.js'])
@endsection
