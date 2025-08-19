<main class="background">
    <div class="text-center">
        <a href="{{ route('home') }}">
            <img class="d-block mx-auto mb-4" src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="" height="57">
        </a>
        <h2>REGISTRASI PASIEN FISIOTERAPI</h2>
        <p class="lead fs-4 pb-2 fw-bolder border-bottom">BUKTI PENDAFTARAN</p>
    </div>

    <div class="alert alert-warning text-center text-dark">
        <span class="fs-5">
            <span class="fw-bolder">PENGUMUMAN : </span><br>
            Pasien Fisioterapi Wajib Membawa <span class="fw-bolder">SURAT PENGANTAR</span> dari Dokter Rehabilitasi Medik.
        </span>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12 mb-1">
                <p class="mb-0"><strong>NORM</strong></p>
                <p class="ms-2"><i class="ri-arrow-right-double-line"></i> {{ $appointmentData['fap_norm'] }}</p>
            </div>
            <div class="col-12 mb-1">
                <p class="mb-0"><strong>NAMA PASIEN</strong></p>
                <p class="ms-2"><i class="ri-arrow-right-double-line"></i> {{ $appointmentData['fap_name'] }}</p>
            </div>
            <div class="col-12 mb-1">
                <p class="mb-0"><strong>TANGGAL PENDAFTARAN</strong></p>
                <p class="ms-2 text-uppercase"><i class="ri-arrow-right-double-line"></i> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $scheduleDateData['sd_date'])->isoFormat('dddd, DD MMMM YYYY') }}</p>
            </div>
            <div class="col-12 mb-1">
                <p class="mb-0"><strong>NAMA KLINIK</strong></p>
                @if($appointmentData['fap_type'] == 'UMUM PAGI')
                    <p class="ms-2 text-uppercase"><i class="ri-arrow-right-double-line"></i> FISIOTERAPI UMUM PAGI</p>
                @elseif($appointmentData['fap_type'] == 'UMUM SORE')
                    <p class="ms-2 text-uppercase"><i class="ri-arrow-right-double-line"></i> FISIOTERAPI UMUM SORE</p>
                @elseif($appointmentData['fap_type'] == 'BPJS PAGI')
                    <p class="ms-2 text-uppercase"><i class="ri-arrow-right-double-line"></i> FISIOTERAPI BPJS PAGI</p>
                @else
                    <p class="ms-2 text-uppercase"><i class="ri-arrow-right-double-line"></i> FISIOTERAPI BPJS SORE</p>
                @endif
            </div>
            <div class="col-12 mb-1">
                <p class="mb-0"><strong>NO ANTRIAN</strong></p>
                <p class="ms-2 "><i class="ri-arrow-right-double-line"></i> {{ $appointmentData['fap_queue'] }}</p>
            </div>
            <div class="col-12 mb-1">
                <p class="mb-0"><strong>WAKTU PENDAFTARAN ULANG</strong></p>
                <p class="ms-2"><i class="ri-arrow-right-double-line"></i> {{ \Carbon\Carbon::createFromFormat('H:i:s', $appointmentData['fap_registration_time'])->format('H:i') }} WIB</p>
            </div>
        </div>
    </div>

    <div class="text-start pt-2 border-top border-bottom">
        <p class="text-break fs-4">Mohon datang tepat waktu sesuai waktu pendaftaran ulang</p>
        <p class="text-break fs-4">Nomor antrian ini adalah nomor antrian tindakan di fisioterapi</p>
        <p class="text-break fs-4">Apabila pasien datang tidak sesuai jam daftar ulang, maka syarat dan ketentuan berlaku</p>
        <p class="text-break fs-4">Pasien fisioterapi wajib membawa surat pengantar dari dokter rehabilitasi medik</p>
        <button class="w-100 btn btn-primary text-uppercase mb-2" wire:click="downloadPdf" wire:loading.attr="disabled">Download Bukti Pendaftaran</button>
        <a href="{{ route('home') }}" class="w-100 btn btn-danger text-uppercase">Kembali Ke Halaman Utama</a>
    </div>
</main>
