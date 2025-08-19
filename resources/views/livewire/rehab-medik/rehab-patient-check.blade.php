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
        <h2>REGISTRASI PASIEN REHAB MEDIK</h2>
        <p class="lead fs-5">Form Registrasi Digunakan Untuk Pasien Yang Ingin Mendaftar ke Klinik Rehab Medik</p>
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
        <div class="mb-3">
            <label class="form-label fs-4">Pilihan Layanan</label>
            <select class="form-select form-select-lg" wire:model.live="patientStatus" required>
                <option value="">Pilihan Layanan</option>
                <option value="lama-umum">Klinik Rehabilitasi Medik - Umum</option>
                <option value="lama-asuransi">Klinik Rehabilitasi Medik - Asuransi / Kontraktor</option>
                <option value="lama-bpjs">Klinik Rehabilitasi Medik - BPJS</option>
                <option value="baru-umum">Klinik Rehabilitasi Medik - Pasien Baru</option>
{{--                <option value="UMUM PAGI">Pasien Fisioterapi - Umum Pagi</option>--}}
{{--                <option value="UMUM SORE">Pasien Fisioterapi - Umum Sore</option>--}}
{{--                <option value="BPJS PAGI">Pasien Fisioterapi - BPJS Pagi</option>--}}
{{--                <option value="BPJS SORE">Pasien Fisioterapi - BPJS Sore</option>--}}
            </select>
        </div>
        @if ($patientStatus == 'lama-umum' || $patientStatus == 'lama-bpjs' || $patientStatus == 'lama-asuransi')
            <form wire:submit.prevent="createAppointmentOld">
                <div class="mb-3">
                    <label for="norm" class="form-label fs-4">No Rekam Medis (NORM)</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="norm" id="norm" wire:model="norm" placeholder="No Medical Record (NORM)" maxlength="8" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                <div class="mb-3">
                    <label for="birthday" class="form-label fs-4">Tanggal Lahir</label>
                    <div x-data="{ birthday: '' }">
                        <input type="text" class="form-control form-control-lg shadow border-0" name="birthday" id="birthday" wire:model="birthday" x-model="birthday" x-mask="99/99/9999" placeholder="dd/mm/yyyy" autocomplete="on" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">No Handphone</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="phone_number" id="phone_number" wire:model="phone_number" placeholder="No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                @if($patientStatus == 'lama-asuransi')
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
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_end_time)->format('H:i') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="w-100 btn btn-primary btn-lg" wire:loading.attr="disabled" {{ !$isOpen ? 'disabled' : '' }}>Submit</button>
            </form>
        @elseif ($patientStatus == 'baru-umum')
            <form wire:submit.prevent="createAppointmentNew">
                <div class="mb-3">
                    <label for="nik" class="form-label fs-4">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="nik" id="nik" wire:model="nik" placeholder="Nomor Induk Kependudukan (NIK)" maxlength="16" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                <div class="mb-3">
                    <label for="birthday" class="form-label fs-4">Tanggal Lahir</label>
                    <div x-data="{ birthday: '' }">
                        <input type="text" class="form-control form-control-lg shadow border-0" name="birthday" id="birthday" wire:model="birthday" x-model="birthday" x-mask="99/99/9999" placeholder="dd/mm/yyyy" autocomplete="on" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Alamat Rumah</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="address" id="address" wire:model="address" placeholder="Alamat Rumah" autofocus autocomplete required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">No Handphone</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="phone_number" id="phone_number" wire:model="phone_number" placeholder="No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autocomplete required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control form-control-lg shadow border-0" name="email" id="email" wire:model="email" placeholder="Alamat Email" autocomplete required>
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
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $session->scd_end_time)->format('H:i') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="w-100 btn btn-primary btn-lg" wire:loading.attr="disabled" {{ !$isOpen ? 'disabled' : '' }}>Submit</button>
            </form>
        @elseif ($patientStatus == 'UMUM PAGI' || $patientStatus == 'UMUM SORE' || $patientStatus == 'BPJS PAGI' || $patientStatus == 'BPJS SORE')
            <div class="alert alert-warning text-center text-dark">
                <span class="fs-5">
                    <span class="fw-bolder">PENGUMUMAN : </span><br>
                    Pasien Fisioterapi Wajib Membawa <span class="fw-bolder">SURAT PENGANTAR</span> dari Dokter Rehabilitasi Medik.
                </span>
            </div>

            <form wire:submit.prevent="createAppointmentFisio">
                <div class="mb-3">
                    <label for="norm" class="form-label fs-4">No Rekam Medis (NORM)</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="norm" id="norm" wire:model="norm" placeholder="No Medical Record (NORM)" maxlength="8" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                <div class="mb-3">
                    <label for="birthday" class="form-label fs-4">Tanggal Lahir</label>
                    <div x-data="{ birthday: '' }">
                        <input type="text" class="form-control form-control-lg shadow border-0" name="birthday" id="birthday" wire:model="birthday" x-model="birthday" x-mask="99/99/9999" placeholder="dd/mm/yyyy" autocomplete="on" required>
                    </div>
                </div>
{{--                <div class="mb-3">--}}
{{--                    <label for="service" class="form-label fs-4">Pilihan Layanan</label>--}}
{{--                    <select class="form-select form-select-lg" name="service" id="service" wire:model="service" required>--}}
{{--                        <option value="" selected>Pilihan Layanan</option>--}}
{{--                        <option value="UMUM PAGI" class="text-uppercase">Pasien Fisioterapi Umum -- Pagi</option>--}}
{{--                        <option value="UMUM SORE" class="text-uppercase">Pasien Fisioterapi Umum -- Sore</option>--}}
{{--                        <option value="BPJS PAGI" class="text-uppercase">Pasien Fisioterapi BPJS -- Pagi</option>--}}
{{--                        <option value="BPJS SORE" class="text-uppercase">Pasien Fisioterapi BPJS -- Sore</option>--}}
{{--                    </select>--}}
{{--                </div>--}}
                <div class="mb-3">
                    <label for="phone_number" class="form-label">No Handphone</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="phone_number" id="phone_number" wire:model="phone_number" placeholder="No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
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
                <button type="submit" class="w-100 btn btn-primary btn-lg" wire:loading.attr="disabled" {{ !$isOpen ? 'disabled' : '' }}>Submit</button>
            </form>
        @endif
    @endif
</main>

@section('script')
    @vite(['resources/js/customs/patient-check-form.js'])
@endsection
