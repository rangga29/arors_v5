@extends('version2.layout')

@section('content')
    <div class="container">
        <div class="background">
            <div class="logo-container">
                <img class="img-responsive center-block" src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="" width="40%">
                <h2 class="logo-title">REGISTRASI PASIEN BARU</h2>
                <p class="logo-text">Form Registrasi Digunakan Untuk Pasien Umum Yang Belum Pernah Berobat di RSCK Sebelumnya</p>
            </div>

            <form action="{{ route('old-baru.appointment-store', $patient['pt_ucode']) }}" method="POST" role="form">
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
                    <label for="nik" class="form-label control-label">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="nik" id="nik" value="{{ $patient['pt_ssn'] }}" readonly>
                </div>
                <div class="form-group">
                    <label for="gender" class="form-label control-label">Jenis Kelamin</label>
                    @if($patient['pt_gender'] == 'M^Laki-Laki')
                        <input type="text" class="form-control form-control-lg shadow border-0" name="gender" id="gender" value="Laki-Laki" readonly>
                    @else
                        <input type="text" class="form-control form-control-lg shadow border-0" name="gender" id="gender" value="Perempuan" readonly>
                    @endif
                </div>
                <div class="form-group">
                    <label for="address" class="form-label control-label">Alamat Rumah</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="address" id="address" placeholder="Alamat Rumah" autofocus autocomplete required>
                </div>
                <div class="form-group">
                    <label for="phone_number" class="form-label control-label">No Handphone</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="phone_number" id="phone_number" placeholder="No Handphone" oninput="this.value = this.value.replace(/\D/g, '');" autocomplete required>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label control-label">Alamat Email</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="email" id="email" placeholder="Alamat Email" autocomplete required>
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
                <button type="submit" class="btn btn-primary btn-lg btn-block">Submit</button>
            </form>
        </div>
    </div>
@endsection
