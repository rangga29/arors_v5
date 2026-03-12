<div>
    <div wire:loading wire:target="createAppointment" id="overlay-form" style="display: none;">
        <div class="d-flex justify-content-center spinner-container">
            <div class="spinner-border" role="status"></div>
        </div>
    </div>

    <form wire:submit.prevent="createAppointment">
        <div class="reg-section-title">
            <i class="ri-user-heart-line"></i> Informasi Pasien
        </div>

        <div class="reg-info-row">
            <span class="reg-info-label">Nama Pasien</span>
            <span class="reg-info-value">{{ $patientData['nama'] }}</span>
        </div>
        <div class="reg-info-row">
            <span class="reg-info-label">Tanggal Lahir</span>
            <span
                class="reg-info-value">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $patientData['tglLahir'])->isoFormat('DD MMMM YYYY') }}</span>
        </div>
        <div class="reg-info-row">
            <span class="reg-info-label">NIK</span>
            <span class="reg-info-value">{{ $patientData['nik'] }}</span>
        </div>
        <div class="reg-info-row" style="margin-bottom: 1.25rem;">
            <span class="reg-info-label">Jenis Kelamin</span>
            <span class="reg-info-value">{{ $patientData['sex'] == 'L' ? 'Laki-Laki' : 'Perempuan' }}</span>
        </div>

        <div class="reg-section-title">
            <i class="ri-calendar-check-line"></i> Detail Pendaftaran
        </div>

        <div class="reg-form-group">
            <label for="address" class="reg-label">Alamat Rumah</label>
            <input type="text" class="reg-input" name="address" id="address" wire:model="address"
                placeholder="Masukkan Alamat Rumah" autofocus autocomplete required>
        </div>
        <div class="reg-form-group">
            <label for="phone_number" class="reg-label">No Handphone</label>
            <input type="text" class="reg-input" name="phone_number" id="phone_number" wire:model="phone_number"
                placeholder="Masukkan No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autocomplete
                required>
        </div>
        <div class="reg-form-group">
            <label for="email" class="reg-label">Alamat Email</label>
            <input type="email" class="reg-input" name="email" id="email" wire:model="email"
                placeholder="Masukkan Alamat Email" autocomplete required>
        </div>

        <div class="reg-form-group">
            <label for="selectedDate" class="reg-label">Tanggal Berobat</label>
            <select class="reg-select" id="selectedDate" wire:model.live="selectedDate" required>
                <option value="">Pilih Tanggal Berobat</option>
                @foreach ($dates as $date)
                    <option value="{{ $date->id }}" {{ $date->sd_is_holiday ? 'disabled' : '' }}>
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
                            <option value="{{ $doctor->sc_doctor_code }}">{{ $doctor->sc_doctor_name }}</option>
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
                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_start_time)->format('H:i') }} -
                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_end_time)->format('H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($serviceType == 'asuransi')
            <div class="reg-form-group">
                <label for="selectedBusinessPartner" class="reg-label">Instansi / Asuransi</label>
                <select class="reg-select" id="selectedBusinessPartner" wire:model="selectedBusinessPartner" required>
                    <option value="">Pilih Instansi / Asuransi</option>
                    @foreach ($businessPartners as $businessPartner)
                        @if ($businessPartner['bp_code'] !== 'BP00008' && $businessPartner['bp_code'] !== 'BA00031')
                            <option value="{{ $businessPartner['bp_code'] }}">{{ $businessPartner['bp_name'] }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
        @endif

        <button type="submit" class="reg-submit-btn" wire:loading.attr="disabled">
            <i class="ri-send-plane-line"></i> Submit
        </button>
    </form>
</div>
