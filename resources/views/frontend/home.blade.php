<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan">
    <meta name="description" content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan">
    <meta name="keywords" content="rsck, cahya kawaluyan, rumah sakit, rs cahya kawaluyan, registrasi online, registrasi rsck, registrasi online rsck">
    <meta name="author" content="RS Cahya Kawaluyan">

    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan">
    <meta property="og:description" content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan">
    <meta property="og:site_name" content="Registrasi Online RS Cahya Kawaluyan">
    <meta property="og:url" content="{{ url()->current() }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan">
    <meta name="twitter:description" content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan">

    <meta name="robots" content="index,follow" />
    <meta name="googlebot" content="index,follow" />
    <meta name="revisit-after" content="2 days" />
    <meta name="author" content="RS Cahya Kawaluyan">
    <meta name="expires" content="never" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <meta name="google-site-verification" content="F9msF55YBBNLSpkRadVhLIbIPl8ronXXjILV-D4yxII" />

    <link rel="shortcut icon" href="{{ asset('images/rsck_trans.png') }}">

    <title>Registrasi Online Rumah Sakit Cahya Kawaluyan</title>

    @vite(['resources/scss/app.scss', 'resources/scss/icons.scss', 'resources/js/head.js'])
</head>
<body class="bg-home">
    <div class="container my-5">
        <header class="d-flex align-items-center pb-3 mb-3">
            <a href="/" class="d-flex align-items-center text-body-emphasis text-decoration-none">
                <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="Logo" class="logo-image">
            </a>
        </header>
        <main>
            <h1 class="text-body-emphasis text-center text-uppercase fw-bolder">Aplikasi Registrasi Online Rumah Sakit Cahya Kawaluyan</h1>
            <div class="container mt-4 mb-5">
                <div class="row justify-content-center">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <a href="{{ route('umum') }}" class="btn btn-one btn-lg px-2 mb-2 fs-4 fw-bolder d-block custom-home-btn">PASIEN UMUM / ASURANSI</a>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('rehab-medik-fisioterapi') }}" class="btn btn-two btn-lg px-2 mb-2 fs-4 fw-bolder d-block custom-home-btn">PASIEN REHAB MEDIK & FISIOTERAPI</a>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('baru') }}" class="btn btn-five btn-lg px-2 mb-2 fs-4 fw-bolder d-block custom-home-btn">PASIEN BARU UMUM</a>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('sunday-clinic') }}" class="btn btn-three btn-lg px-2 mb-4 fs-4 fw-bolder d-block custom-home-btn">PASIEN SUNDAY CLINIC</a>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('batal-antrian.norm') }}" class="btn btn-four btn-lg px-2 mb-2 fs-4 fw-bolder d-block custom-home-btn">PEMBATALAN NOMOR ANTRIAN</a>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('cek-antrian.norm') }}" class="btn btn-six btn-lg px-2 mb-2 fs-4 fw-bolder d-block custom-home-btn">CEK NOMOR ANTRIAN</a>
                        </div>
                        <div class="col-12">
                            <a href="https://www.rscahyakawaluyan.com/doctors" class="btn btn-six btn-lg px-2 mb-4 fs-4 fw-bolder d-block custom-home-btn" target="_blank">CEK JADWAL DOKTER</a>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-danger text-center">
                            <span class="fs-5">
                                <span class="fw-bolder">PENGUMUMAN : </span><br>
                                Mulai Tanggal <span class="fw-bolder">10 Oktober 2024</span> Pasien BPJS (Kecuali Pasien Rehabilitasi Medik) <span class="fw-bolder">WAJIB</span> Menggunakan Aplikasi Mobile JKN.
                            </span>
                        </div>
                        </div>
                        <div class="col-12">
                            <a href="https://play.google.com/store/apps/details?id=app.bpjs.mobile&hl=en&gl=US" target="_blank" class="btn btn-two btn-lg px-2 mb-2 fs-4 fw-bolder d-block custom-home-btn">LAYANAN MOBILE JKN</a>
                        </div>
                        <div class="col-12">
                            <a href="https://registrasi.rscahyakawaluyan.com/images/tatacara_mobilejkn.jpg" target="_blank" class="btn btn-two btn-lg px-2 mb-4 fs-4 fw-bolder d-block custom-home-btn">TATA CARA MOBILE JKN</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    @vite(['resources/js/app.js', 'resources/js/layout.js'])
</body>
</html>
