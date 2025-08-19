<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <title>Informasi Rumah Sakit & Billing Carolus</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" type="text/css" href="{{ asset('bars/styles/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('bars/styles/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('bars/fonts/css/fontawesome-all.min.css') }}">
    <link rel="manifest" href="{{ asset('bars/_manifest.json') }}" data-pwa-version="set_in_manifest_and_pwa_js">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/rsck_logo.png') }}">
</head>

<body class="theme-light">

<div id="preloader"><div class="spinner-border color-highlight" role="status"></div></div>

<div id="page">

    <div class="header header-fixed header-logo-left mb-3 shadow-l">
        <a href="{{ route('carolus.menu', ['qrc_room' => $qrc_room, 'qrc_ucode' => $qrc_ucode]) }}" class="header-logo"></a>
    </div>

    <div class="page-content header-clear">
        <div class="card mb-0 bg-3" data-card-height="cover-full">
            <div class="card-shadow">
                <div class="content">
                    <div class="ad-300x50 mb-1">
                        <div class="card bg-1" data-card-height="50"></div>
                    </div>
                </div>

                <div class="content">
                    <h1 class="text-center">LOGIN INFORMASI BILLING</h1>
                    <p class="font-18 text-center text-black mt-2 fw-bolder">CAROLUS - KAMAR {{ preg_replace('/ /', ' - ', $qrc_room, 1) }}</p>
                    <div class="divider-icon divider-margins"><i class="fa font-24 color-dark-dark fa-procedures"></i></div>

                    @error('password')
                        <div class="ms-3 me-3 alert alert-small rounded-s shadow-s bg-yellow-dark" role="alert">
                            <span><i class="fa fa-exclamation-triangle color-black"></i></span>
                            <strong class="color-black">{{ $message }}</strong>
                        </div>
                    @enderror

                    <form action="{{ route('carolus.billing-information.check', ['qrc_room' => $qrc_room, 'qrc_ucode' => $qrc_ucode]) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="px-3 pb-1">
                            <div class="position-relative mt-2">
                                <i class="fa fa-lock position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                <input type="password" name="password" class="form-control ps-5" id="form3a" placeholder="Password" required>
                            </div>
                            <button type="submit" class="back-button btn btn-center-s btn-s rounded-s bg-green-dark font-700 text-uppercase mb-2 mt-4">LOGIN</button>
                            <a href="{{ route('carolus.menu', ['qrc_room' => $qrc_room, 'qrc_ucode' => $qrc_ucode]) }}" class="back-button btn btn-center-m btn-s rounded-s bg-blue-light font-700 text-uppercase mb-3 mt-2">KEMBALI KE MENU</a>
                        </div>
                    </form>

                    <p class="boxed-text-xl mt-n3 text-black fw-bolder font-14">
                        * Untuk Password Login Informasi Billing Carolus Kamar {{ preg_replace('/ /', ' - ', $qrc_room, 1) }} Silahkan Hubungi Perawat Carolus.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ asset('bars/scripts/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('bars/scripts/custom.js') }}"></script>
</body>
