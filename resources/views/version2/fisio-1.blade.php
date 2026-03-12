@extends('version2.layout')

@section('content')
    <div class="container">
        <div class="background">
            <div class="logo-container">
                <img class="img-responsive center-block" src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="" width="40%">
                <h2 class="logo-title">REGISTRASI PASIEN FISIOTERAPI</h2>
                <p class="logo-text">Form Registrasi Dikhususkan Untuk Pasien Fisioterapi</p>
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

            <form action="{{ route('old-fisioterapi.appointment-store') }}" method="POST" role="form">
                @csrf
                <div class="form-group">
                    <label for="norm" class="form-label control-label">No Rekam Medis (NORM)</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="norm" id="norm" placeholder="No Medical Record (NORM)" maxlength="6" oninput="this.value = this.value.replace(/\D/g, '');" autofocus autocomplete required>
                </div>
                <div class="form-group">
                    <label for="birthday" class="form-label control-label">Tanggal Lahir</label>
                    <input type="text" class="form-control form-control-lg shadow border-0" name="birthday" id="birthday" placeholder="dd/mm/yyyy" autocomplete required>
                    <small class="form-note">* Gunakan Tanda (/) Contoh 22/09/2022</small>
                </div>
                <div class="form-group">
                    <label for="service" class="form-label control-label">Pilihan Layanan</label>
                    <select class="form-control form-control-lg shadow border-0" name="service" id="service" wire:model="service" required>
                        <option value="" selected>Pilihan Layanan</option>
                        <option value="UMUM PAGI" class="text-uppercase">Pasien Fisioterapi Umum -- Pagi</option>
                        <option value="UMUM SORE" class="text-uppercase">Pasien Fisioterapi Umum -- Sore</option>
                        <option value="BPJS PAGI" class="text-uppercase">Pasien Fisioterapi BPJS -- Pagi</option>
                        <option value="BPJS SORE" class="text-uppercase">Pasien Fisioterapi BPJS -- Sore</option>
                    </select>
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
                <button type="submit" class="btn btn-primary btn-lg btn-block">Submit</button>
            </form>
        </div>
    </div>
@endsection
