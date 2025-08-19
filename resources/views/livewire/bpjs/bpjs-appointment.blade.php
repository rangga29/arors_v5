<div>
    <div wire:loading wire:target="createAppointment" id="overlay-form" style="display: none;">
        <div class="d-flex justify-content-center spinner-container">
            <div class="spinner-border" role="status"></div>
        </div>
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
        <div class="mb-1">
            <label for="no_bpjs" class="form-label">No Kartu BPJS</label>
            <input type="text" class="form-control form-control-lg shadow border-0" name="no_bpjs" id="no_bpjs" value="{{ $bpjsData['peserta']['noKartu'] }}" readonly>
        </div>
        <hr>
        <div class="mb-3">
            <label for="phone_number" class="form-label">No Handphone</label>
            <input type="text" class="form-control form-control-lg shadow border-0" name="phone_number" id="phone_number" wire:model="phone_number" placeholder="No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
        </div>

        <div class="mb-3">
            <label for="selectedDate" class="form-label">Tanggal Berobat</label>
            <select class="form-select form-select-lg" id="selectedDate" wire:model="selectedDate" required>
                <option value="">Pilih Tanggal Berobat</option>
                @foreach($dates as $date)
                    <option value="{{ $date->id }}" {{ $date->sd_is_holiday ? 'disabled' : '' }}>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}</option>
                @endforeach
            </select>
        </div>

        @if($bpjsData['poliRujukan']['kode'] == 'INT')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK PENYAKIT DALAM PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @elseif($bpjsData['poliRujukan']['kode'] == 'ANA')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK KESEHATAN ANAK PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @elseif($bpjsData['poliRujukan']['kode'] == 'SAR')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK SYARAF PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @elseif($bpjsData['poliRujukan']['kode'] == 'BED')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK BEDAH UMUM PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @elseif($bpjsData['poliRujukan']['kode'] == 'ORT')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK BEDAH ORTHOPEDI PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @elseif($bpjsData['poliRujukan']['kode'] == 'KLT')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK DERMATOLOGI V & E PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @elseif($bpjsData['poliRujukan']['kode'] == 'JIW')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK PSIKIATRI PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @elseif($bpjsData['poliRujukan']['kode'] == 'JAN')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK JANTUNG PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @elseif($bpjsData['poliRujukan']['kode'] == 'OBG')
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">KLINIK OBSTETRI & GINEKOLOGI PAGI DAN SORE DAPAT MELAKUKAN PENDAFTARAN MELALUI APLIKASI MOBILE JKN</p>
            <p class="lead fs-5 fw-bolder text-dark text-bg-warning">INFORMASI SELANJUTNYA DAPAT DILIHAT DI <a href="https://www.instagram.com/p/C-Y84TFymzC/?igsh=dzlvODhrZ3VuaWRk" target="_blank">LINK INI</a></p>
        @endif

        <div class="mb-3">
            <label for="selectedClinic" class="form-label">Klinik</label>
            <select class="form-select form-select-lg" id="selectedClinic" wire:model.live="selectedClinic" required>
                <option value="">Pilih Klinik</option>
                @foreach($clinics as $clinic)
                    <option value="{{ $clinic->cl_code }}">{{ $clinic->cl_name }}</option>
                @endforeach
            </select>
        </div>

        @if ($selectedClinic)
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

        @if ($selectedDoctor)
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

        <button type="submit" class="w-100 btn btn-primary btn-lg" wire:loading.attr="disabled">Submit</button>
    </form>
</div>
