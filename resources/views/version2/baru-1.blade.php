@extends('version2.layout')

@section('content')
    <div class="container">
        <div class="background">
            <div class="logo-container">
                <img class="img-responsive center-block" src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="" width="40%">
                <h2 class="logo-title">REGISTRASI PASIEN BARU</h2>
                <p class="logo-text">Form Registrasi Digunakan Untuk Pasien Umum Yang Belum Pernah Berobat di RSCK Sebelumnya</p>
                @if(!$isOpen)
                    <div class="alert alert-danger">
                        @if($currentHour < env('CLOSE_HOUR', '18') && $currentHour <= env('OPEN_HOUR', '7'))
                            <p>Registrasi Untuk Tanggal {{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointmentDate)->isoFormat('dddd, DD MMMM YYYY')  }} Belum Dibuka</p>
                        @else
                            <p>Registrasi Untuk Tanggal {{ \Carbon\Carbon::createFromFormat('Y-m-d', $todayDate)->isoFormat('dddd, DD MMMM YYYY')  }} Sudah Ditutup</p>
                        @endif
                    </div>
                @endif
            </div>

            @if (session()->has('error'))
                <div class="alert alert-danger">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('old-baru.patient-check') }}" method="POST" role="form">
                @csrf
                <div class="form-group">
                    <label for="nik" class="form-label control-label">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="nik" id="nik" placeholder="Nomor Induk Kependudukan (NIK)" maxlength="16" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                <div class="form-group">
                    <label for="birthday" class="form-label control-label">Tanggal Lahir</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="birthday" id="birthday" placeholder="dd/mm/yyyy" autocomplete required>
                    <small class="form-note">* Gunakan Tanda (/) Contoh 22/09/2022</small>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Cek Data</button>
            </form>
        </div>
    </div>
@endsection
