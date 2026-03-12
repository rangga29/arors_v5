<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta name="description"
        content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta name="keywords"
        content="rsck, cahya kawaluyan, rumah sakit, rs cahya kawaluyan, registrasi online, registrasi rsck, registrasi online rsck, registrasi sunday clinic">
    <meta name="author" content="RS Cahya Kawaluyan">

    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta property="og:description"
        content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta property="og:site_name" content="Registrasi Online RS Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta property="og:url" content="{{ url()->current() }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">
    <meta name="twitter:description"
        content="Website Resmi Registrasi Online Rumah Sakit Cahya Kawaluyan Rehabilitasi Medik & Fisioterapi">

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
    <div class="container home-container">
        {{-- Header --}}
        <header class="home-header">
            <a href="/">
                <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan" class="logo-image">
            </a>
            <h1>Registrasi Klinik Rehabilitasi Medik & Fisioterapi</h1>
        </header>

        <main>
            {{-- Section: Pilihan Klinik --}}
            <div class="home-section">
                <div class="home-section-title">
                    <i class="ri-heart-pulse-line"></i> Pilihan Klinik
                </div>
                <div class="home-btn-group">
                    <a href="{{ route('rehab-medik') }}" class="home-btn home-btn-two">
                        <i class="ri-stethoscope-line"></i>
                        <span>Klinik Rehabilitasi Medik</span>
                    </a>
                    <a href="{{ route('fisioterapi') }}" class="home-btn home-btn-five">
                        <i class="ri-walk-line"></i>
                        <span>Fisioterapi</span>
                    </a>
                    {{-- <a href="{{ route('terapi-okupasi') }}" class="home-btn home-btn-six">
                        <i class="ri-hand-heart-line"></i>
                        <span>Terapi Okupasi</span>
                    </a>
                    <a href="{{ route('terapi-wicara') }}" class="home-btn home-btn-three">
                        <i class="ri-chat-voice-line"></i>
                        <span>Terapi Wicara</span>
                    </a> --}}
                </div>
            </div>
        </main>

        <footer class="home-footer">
            <p>&copy; {{ date('Y') }} RS Cahya Kawaluyan. All rights reserved.</p>
        </footer>
    </div>
    @vite(['resources/js/app.js', 'resources/js/layout.js'])
</body>

</html>
