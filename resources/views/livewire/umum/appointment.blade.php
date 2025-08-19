<div>
    <div wire:loading wire:target="createAppointment" id="overlay-form" style="display: none;">
        <div class="d-flex justify-content-center spinner-container">
            <div class="spinner-border" role="status"></div>
        </div>
    </div>

    <div class="alert alert-danger text-center">
        <span class="fs-5">
            Anda Terdaftar Sebagai <span class="fw-bolder">PASIEN NON BPJS</span> dan Nomor Antrian <span class="fw-bolder">TIDAK DAPAT</span> Digunakan Untuk Mendaftar Sebagai Pasien BPJS <span class="fw-bolder">KECUALI</span> Pasien Klinik Rehabilitasi Medik
        </span>
    </div>

    <form wire:submit.prevent="createAppointment">
        <div class="mb-1">
            <label for="name" class="form-label">Nama Pasien</label>
            <input type="text" class="form-control form-control-lg shadow border-0" name="name" id="name" value="{{ $patientData['FullName'] }}" readonly>
        </div>
        <div class="mb-1">
            <label for="date_of_birth" class="form-label">Tanggal Lahir</label>
            <input type="text" class="form-control form-control-lg shadow border-0" name="date_of_birth" id="date_of_birth" value="{{ \Carbon\Carbon::createFromFormat('Ymd', $patientData['DateOfBirth'])->isoFormat('DD MMMM YYYY') }}" readonly>
        </div>
        <hr>
        <div class="mb-3">
            <label for="phone_number" class="form-label">No Handphone</label>
            <input type="text" class="form-control form-control-lg shadow border-0" name="phone_number" id="phone_number" wire:model="phone_number" placeholder="No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
        </div>

        <div class="mb-3">
            <label for="selectedDate" class="form-label">Tanggal Berobat</label>
            <select class="form-select form-select-lg" id="selectedDate" wire:model.live="selectedDate" required>
                <option value="">Pilih Tanggal Berobat</option>
                @foreach($dates as $date)
                    <option value="{{ $date->id }}" {{ $date->sd_is_holiday ? 'disabled' : '' }}>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}</option>
                @endforeach
            </select>
        </div>

        @if ($selectedDate)
            <div class="mb-3">
                <label for="selectedClinic" class="form-label">Klinik</label>
                <select class="form-select form-select-lg" id="selectedClinic" wire:model.live="selectedClinic" required>
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
            <div class="mb-3">
                <label for="selectedDoctor" class="form-label">Dokter</label>
                <select class="form-select form-select-lg" id="selectedDoctor" wire:model.live="selectedDoctor" required>
                    <option value="">Pilih Dokter</option>
                    @php
                        $uniqueDoctorCodes = [];
                    @endphp
                    @foreach($doctors as $doctor)
                        @if (!in_array($doctor->sc_doctor_code, $uniqueDoctorCodes))
                            <option value="{{ $doctor->sc_doctor_code }}">{{ $doctor->sc_doctor_name }}</option>
                            @php
                                $uniqueDoctorCodes[] = $doctor->sc_doctor_code;
                            @endphp
                        @endif
                    @endforeach
                </select>
            </div>
        @endif

        @if ($selectedDoctor && $sessions->isNotEmpty())
            <div class="mb-3">
                <label for="selectedSession" class="form-label">Sesi</label>
                <select class="form-select form-select-lg" id="selectedSession" wire:model="selectedSession" required>
                    <option value="">Pilih Sesi</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">
                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_start_time)->format('H:i') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_end_time)->format('H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($serviceType == 'asuransi')
            <div class="mb-3">
                <label for="selectedBusinessPartner" class="form-label">Instansi / Asuransi</label>
                <select class="form-select form-select-lg" id="selectedBusinessPartner" wire:model="selectedBusinessPartner" required>
                    <option value="">Pilih Instansi / Asuransi</option>
                    @foreach($businessPartners as $businessPartner)
                        @if($businessPartner['bp_code'] !== 'BP00008' && $businessPartner['bp_code'] !== 'BA00031')
                            <option value="{{ $businessPartner['bp_code'] }}">{{ $businessPartner['bp_name'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        @endif

        <button type="submit" class="w-100 btn btn-primary btn-lg" wire:loading.attr="disabled">Submit</button>
    </form>
</div>
