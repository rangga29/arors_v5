<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta name="description" content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta name="keywords" content="rsck, cahya kawaluyan, rumah sakit, rs cahya kawaluyan, registrasi online, registrasi rsck, registrasi online rsck, registrasi sunday clinic">
    <meta name="author" content="RS Cahya Kawaluyan">

    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta property="og:description" content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta property="og:site_name" content="Registrasi Online RS Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta property="og:url" content="{{ url()->current() }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta name="twitter:description" content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">

    <meta name="robots" content="index,follow" />
    <meta name="googlebot" content="index,follow" />
    <meta name="revisit-after" content="2 days" />
    <meta name="author" content="RS Cahya Kawaluyan">
    <meta name="expires" content="never" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <link rel="shortcut icon" href="{{ asset('images/rsck_trans.png') }}">

    <title>Registrasi Online Rumah Sakit Cahya Kawaluyan - Rehabilitasi Medik & Fisioterapi</title>

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
        <h1 class="text-body-emphasis text-center text-uppercase fw-bolder">Registrasi Rehabilitasi Medik & Fisioterapi</h1>
        <div class="container mt-4 mb-5">
            <div class="row justify-content-center">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <a href="{{ route('rehab-medik') }}" class="btn btn-tertiary btn-lg px-2 mb-2 fs-4 fw-bolder d-block custom-home-btn">KLINIK REHABILITASI MEDIK</a>
                    </div>
                    <div class="col-12">
                        <a href="{{ route('fisioterapi') }}" class="btn btn-four btn-lg px-2 mb-4 fs-4 fw-bolder d-block custom-home-btn">FISIOTERAPI</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@vite(['resources/js/app.js', 'resources/js/layout.js'])
</body>
</html>
