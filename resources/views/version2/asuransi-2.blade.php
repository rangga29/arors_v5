@extends('version2.layout')

@section('content')
    <div class="container">
        <div class="background">
            <div class="logo-container">
                <img class="img-responsive center-block" src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="" width="40%">
                <h2 class="logo-title">REGISTRASI PASIEN ASURANSI / KONTRAKTOR</h2>
                <p class="logo-text">Form Registrasi Digunakan Untuk Pasien Asuransi / Kontraktor Yang Sudah Memiliki Nomor Rekam Medis (NORM)</p>
            </div>

            <form action="{{ route('old-asuransi.appointment-store', $patient['pt_ucode']) }}" method="POST" role="form">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label control-label">Nama Pasien</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="name" id="name" value="{{ $patient['pt_name'] }}" readonly>
                </div>
                <div class="form-group">
                    <label for="date_of_birth" class="form-label control-label">Tanggal Lahir</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="date_of_birth" id="date_of_birth" value="{{ \Carbon\Carbon::createFromFormat('Y-m-d', $patient['pt_birthday'])->isoFormat('DD MMMM YYYY') }}" readonly>
                </div>
                <div class="form-group">
                    <label for="phone_number" class="form-label control-label">No Handphone</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="phone_number" id="phone_number" placeholder="No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                <div class="form-group">
                    <label for="selectedDate" class="form-label control-label">Tanggal Berobat</label>
                    @foreach($dates as $date)
                        <input type="text" class="form-control form-control-lg shadow border-0" name="selectedDate" id="selectedDate" value="{{ \Carbon\Carbon::createFromFormat('Y-m-d', $date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}" readonly>
                    @endforeach
                </div>
                <div class="form-group">
                    <label for="selectedSchedule" class="form-label control-label">Klinik Dokter</label>
                    <select class="form-control form-control-lg shadow border-0" name="selectedSchedule" id="selectedSchedule" required>
                        <option value="">Pilih Klinik Dokter</option>
                        @foreach ($clinicsWithDoctors as $clinicName => $schedules)
                            <optgroup label="{{ $clinicName }}" class="optgroup-custom">
                                @foreach ($schedules as $schedule)
                                    @foreach($schedule->scheduleDetails as $scheduleDetail)
                                        @if($scheduleDetail->scd_available && $scheduleDetail->scd_umum)
                                            <option value="{{ $scheduleDetail->id }}">
                                                {{ $schedule->sc_doctor_name }} [{{ \Carbon\Carbon::createFromFormat('H:i:s', $scheduleDetail->scd_start_time)->format('H:i') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $scheduleDetail->scd_end_time)->format('H:i') }}]
                                            </option>
                                        @endif
                                    @endforeach
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="selectedBusinessPartner" class="form-label">Instansi / Asuransi</label>
                    <select class="form-control form-control-lg shadow border-0" name="selectedBusinessPartner" id="selectedBusinessPartner" required>
                        <option value="">Pilih Instansi / Asuransi</option>
                        @foreach($businessPartners as $businessPartner)
                            <option value="{{ $businessPartner['bp_code'] }}">{{ $businessPartner['bp_name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Submit</button>
            </form>
        </div>
    </div>
@endsection
