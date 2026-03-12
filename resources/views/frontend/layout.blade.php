<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan {{$subTitle}}">
    <meta name="description" content="{{$description}}">
    <meta name="keywords" content="rsck, cahya kawaluyan, rumah sakit, rs cahya kawaluyan, registrasi online, registrasi rsck, registrasi online rsck, {{$subKeywords}}">
    <meta name="author" content="RS Cahya Kawaluyan">

    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan {{$subTitle}}">
    <meta property="og:description" content="{{$description}}">
    <meta property="og:site_name" content="Registrasi Online RS Cahya Kawaluyan">
    <meta property="og:url" content="{{ url()->current() }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Registrasi Online Rumah Sakit Cahya Kawaluyan {{$subTitle}}">
    <meta name="twitter:description" content="{{$description}}">

    <meta name="robots" content="index,follow" />
    <meta name="googlebot" content="index,follow" />
    <meta name="revisit-after" content="2 days" />
    <meta name="author" content="RS Cahya Kawaluyan">
    <meta name="expires" content="never" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <meta name="google-site-verification" content="F9msF55YBBNLSpkRadVhLIbIPl8ronXXjILV-D4yxII" />

    <link rel="shortcut icon" href="{{ asset('images/rsck_trans.png') }}">

    <title>Registrasi Online Rumah Sakit Cahya Kawaluyan - {{$subTitle}}</title>

    @vite(['resources/scss/app.scss', 'resources/scss/icons.scss', 'resources/js/head.js'])
    @yield('css')
    @livewireStyles
</head>
<body class="bg-home @if($type === 'umum') bg-umum @elseif($type === 'bpjs') bg-bpjs @elseif($type === 'fisioterapi') bg-fisioterapi @elseif($type === 'rehab-medik') bg-rehab-medik @endif">
    <div class="container">
        {{ $slot }}
    </div>

    @vite(['resources/js/app.js', 'resources/js/layout.js'])
    @yield('script')
    @livewireScripts
</body>
</html>


