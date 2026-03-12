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
            <h2>REGISTRASI PASIEN REHAB MEDIK</h2>
            <span class="reg-badge">Form Registrasi</span>
        </div>

        <p class="reg-description">
            Form Registrasi Digunakan Untuk Pasien Yang Ingin Mendaftar ke Klinik Rehab Medik
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

        @if ($isOpen)
            <div class="reg-form-group">
                <label class="reg-label">Pilihan Layanan</label>
                <select class="reg-select" wire:model.live="patientStatus" required>
                    <option value="">Pilih Layanan</option>
                    <option value="lama-umum">Klinik Rehabilitasi Medik - Umum</option>
                    <option value="lama-asuransi">Klinik Rehabilitasi Medik - Asuransi / Kontraktor</option>
                    <option value="lama-bpjs">Klinik Rehabilitasi Medik - BPJS</option>
                    <option value="baru-umum">Klinik Rehabilitasi Medik - Pasien Baru Umum</option>
                    <option value="baru-asuransi">Klinik Rehabilitasi Medik - Pasien Baru Asuransi / Kontraktor
                    </option>
                </select>
            </div>

            @if ($patientStatus == 'lama-umum' || $patientStatus == 'lama-bpjs' || $patientStatus == 'lama-asuransi')
                <form wire:submit.prevent="createAppointmentOld">
                    <div class="reg-section-title">
                        <i class="ri-file-list-3-line"></i> Data Pasien Lama
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
                        <label for="phone_number" class="reg-label">No Handphone</label>
                        <input type="text" class="reg-input" name="phone_number" id="phone_number"
                            wire:model="phone_number" placeholder="Masukkan No Handphone"
                            oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                    </div>
                    @if ($patientStatus == 'lama-asuransi')
                        <div class="reg-form-group">
                            <label for="selectedBusinessPartner" class="reg-label">Instansi / Asuransi</label>
                            <select class="reg-select" id="selectedBusinessPartner" wire:model="selectedBusinessPartner"
                                required>
                                <option value="">Pilih Instansi / Asuransi</option>
                                @foreach ($businessPartners as $businessPartner)
                                    @if ($businessPartner['bp_code'] !== 'BP00008' && $businessPartner['bp_code'] !== 'BA00031')
                                        <option value="{{ $businessPartner['bp_code'] }}">
                                            {{ $businessPartner['bp_name'] }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="reg-section-title">
                        <i class="ri-calendar-check-line"></i> Jadwal Berobat
                    </div>

                    <div class="reg-form-group">
                        <label for="selectedDate" class="reg-label">Tanggal Berobat</label>
                        <select class="reg-select" id="selectedDate" wire:model.live="selectedDate" required>
                            <option value="">Pilih Tanggal Berobat</option>
                            @foreach ($dates as $date)
                                <option value="{{ $date->id }}"
                                    {{ $date->sd_is_holiday || \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isSunday() ? 'disabled' : '' }}>
                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if ($selectedDate)
                        <div class="reg-form-group">
                            <label for="selectedClinic" class="reg-label">Klinik</label>
                            <select class="reg-select" id="selectedClinic" wire:model.live="selectedClinic" required>
                                <option value="">Pilih Klinik</option>
                                @forelse($clinics as $clinic)
                                    <option value="{{ $clinic->cl_code }}">{{ $clinic->cl_name }}</option>
                                @empty
                                    <option disabled>Tidak ada klinik tersedia pada tanggal ini.</option>
                                @endforelse
                            </select>
                        </div>
                    @endif
                    @if ($selectedClinic && $doctors->isNotEmpty())
                        <div class="reg-form-group">
                            <label for="selectedDoctor" class="reg-label">Dokter</label>
                            <select class="reg-select" id="selectedDoctor" wire:model.live="selectedDoctor" required>
                                <option value="">Pilih Dokter</option>
                                @php $uniqueDoctorCodes = []; @endphp
                                @foreach ($doctors as $doctor)
                                    @if (!in_array($doctor->sc_doctor_code, $uniqueDoctorCodes))
                                        <option value="{{ $doctor->sc_doctor_code }}">{{ $doctor->sc_doctor_name }}
                                        </option>
                                        @php $uniqueDoctorCodes[] = $doctor->sc_doctor_code; @endphp
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($selectedDoctor && $sessions->isNotEmpty())
                        <div class="reg-form-group">
                            <label for="selectedSession" class="reg-label">Sesi</label>
                            <select class="reg-select" id="selectedSession" wire:model="selectedSession" required>
                                <option value="">Pilih Sesi</option>
                                @foreach ($sessions as $session)
                                    <option value="{{ $session->id }}">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_start_time)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_end_time)->format('H:i') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <button type="submit" class="reg-submit-btn" wire:loading.attr="disabled"
                        {{ !$isOpen ? 'disabled' : '' }}>
                        <i class="ri-send-plane-line"></i> Submit
                    </button>
                </form>
            @elseif ($patientStatus == 'baru-umum' || $patientStatus == 'baru-asuransi')
                <form wire:submit.prevent="createAppointmentNew">
                    <div class="reg-section-title">
                        <i class="ri-file-list-3-line"></i> Data Pasien Baru
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
                            <input type="text" class="reg-input" name="birthday" id="birthday"
                                wire:model="birthday" x-model="birthday" x-mask="99/99/9999"
                                placeholder="dd/mm/yyyy" autocomplete="on" required>
                        </div>
                    </div>
                    <div class="reg-form-group">
                        <label for="address" class="reg-label">Alamat Rumah</label>
                        <input type="text" class="reg-input" name="address" id="address" wire:model="address"
                            placeholder="Masukkan Alamat Rumah" autofocus autocomplete required>
                    </div>
                    <div class="reg-form-group">
                        <label for="phone_number" class="reg-label">No Handphone</label>
                        <input type="text" class="reg-input" name="phone_number" id="phone_number"
                            wire:model="phone_number" placeholder="Masukkan No Handphone"
                            oninput="this.value = this.value.replace(/\D/g, '');" autocomplete required>
                    </div>
                    <div class="reg-form-group">
                        <label for="email" class="reg-label">Alamat Email</label>
                        <input type="email" class="reg-input" name="email" id="email" wire:model="email"
                            placeholder="Masukkan Alamat Email" autocomplete required>
                    </div>
                    @if ($patientStatus == 'baru-asuransi')
                        <div class="reg-form-group">
                            <label for="selectedBusinessPartner" class="reg-label">Instansi / Asuransi</label>
                            <select class="reg-select" id="selectedBusinessPartner"
                                wire:model="selectedBusinessPartner" required>
                                <option value="">Pilih Instansi / Asuransi</option>
                                @foreach ($businessPartners as $businessPartner)
                                    @if ($businessPartner['bp_code'] !== 'BP00008' && $businessPartner['bp_code'] !== 'BA00031')
                                        <option value="{{ $businessPartner['bp_code'] }}">
                                            {{ $businessPartner['bp_name'] }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="reg-section-title">
                        <i class="ri-calendar-check-line"></i> Jadwal Berobat
                    </div>

                    <div class="reg-form-group">
                        <label for="selectedDate" class="reg-label">Tanggal Berobat</label>
                        <select class="reg-select" id="selectedDate" wire:model.live="selectedDate" required>
                            <option value="">Pilih Tanggal Berobat</option>
                            @foreach ($dates as $date)
                                <option value="{{ $date->id }}"
                                    {{ $date->sd_is_holiday || \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isSunday() ? 'disabled' : '' }}>
                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if ($selectedDate)
                        <div class="reg-form-group">
                            <label for="selectedClinic" class="reg-label">Klinik</label>
                            <select class="reg-select" id="selectedClinic" wire:model.live="selectedClinic" required>
                                <option value="">Pilih Klinik</option>
                                @forelse($clinics as $clinic)
                                    <option value="{{ $clinic->cl_code }}">{{ $clinic->cl_name }}</option>
                                @empty
                                    <option disabled>Tidak ada klinik tersedia pada tanggal ini.</option>
                                @endforelse
                            </select>
                        </div>
                    @endif
                    @if ($selectedClinic && $doctors->isNotEmpty())
                        <div class="reg-form-group">
                            <label for="selectedDoctor" class="reg-label">Dokter</label>
                            <select class="reg-select" id="selectedDoctor" wire:model.live="selectedDoctor" required>
                                <option value="">Pilih Dokter</option>
                                @php $uniqueDoctorCodes = []; @endphp
                                @foreach ($doctors as $doctor)
                                    @if (!in_array($doctor->sc_doctor_code, $uniqueDoctorCodes))
                                        <option value="{{ $doctor->sc_doctor_code }}">{{ $doctor->sc_doctor_name }}
                                        </option>
                                        @php $uniqueDoctorCodes[] = $doctor->sc_doctor_code; @endphp
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($selectedDoctor && $sessions->isNotEmpty())
                        <div class="reg-form-group">
                            <label for="selectedSession" class="reg-label">Sesi</label>
                            <select class="reg-select" id="selectedSession" wire:model="selectedSession" required>
                                <option value="">Pilih Sesi</option>
                                @foreach ($sessions as $session)
                                    <option value="{{ $session->id }}">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_start_time)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_end_time)->format('H:i') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <button type="submit" class="reg-submit-btn" wire:loading.attr="disabled"
                        {{ !$isOpen ? 'disabled' : '' }}>
                        <i class="ri-send-plane-line"></i> Submit
                    </button>
                </form>
            @elseif (
                $patientStatus == 'UMUM PAGI' ||
                    $patientStatus == 'UMUM SORE' ||
                    $patientStatus == 'BPJS PAGI' ||
                    $patientStatus == 'BPJS SORE')
                <div class="reg-announcement reg-announcement-warning">
                    <div class="reg-announcement-content">
                        <strong>PENGUMUMAN :</strong><br>
                        Pasien Fisioterapi Wajib Membawa <strong>SURAT PENGANTAR</strong> dari Dokter
                        Rehabilitasi Medik.
                    </div>
                </div>

                <form wire:submit.prevent="createAppointmentFisio">
                    <div class="reg-section-title">
                        <i class="ri-file-list-3-line"></i> Data Pasien Fisioterapi
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
                            <input type="text" class="reg-input" name="birthday" id="birthday"
                                wire:model="birthday" x-model="birthday" x-mask="99/99/9999"
                                placeholder="dd/mm/yyyy" autocomplete="on" required>
                        </div>
                    </div>
                    <div class="reg-form-group">
                        <label for="phone_number" class="reg-label">No Handphone</label>
                        <input type="text" class="reg-input" name="phone_number" id="phone_number"
                            wire:model="phone_number" placeholder="Masukkan No Handphone"
                            oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                    </div>
                    <div class="reg-form-group">
                        <label for="selectedDate" class="reg-label">Tanggal Berobat</label>
                        <select class="reg-select" id="selectedDate" wire:model.live="selectedDate" required>
                            <option value="" selected>Pilih Tanggal Berobat</option>
                            @foreach ($dates as $date)
                                <option value="{{ $date->sd_ucode }}" {{ $date->sd_is_holiday ? 'disabled' : '' }}>
                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="reg-submit-btn" wire:loading.attr="disabled"
                        {{ !$isOpen ? 'disabled' : '' }}>
                        <i class="ri-send-plane-line"></i> Submit
                    </button>
                </form>
            @endif
        @endif
    </div>
</main>

@section('script')
    @vite(['resources/js/customs/patient-check-form.js'])
@endsection
