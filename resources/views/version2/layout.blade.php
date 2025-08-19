<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="{{ asset('images/rsck_trans.png') }}">

    <title>Registrasi Online Rumah Sakit Cahya Kawaluyan Versi 2</title>

    <link rel="stylesheet" href="{{ asset('bootstrap-3.4.1-dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bootstrap-3.4.1-dist/css/bootstrap-theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bootstrap-3.4.1-dist/css/style.css') }}">

    <style>
        .background-umum {
            background-image: url({{ asset('images/bg_umum_2024.jpg') }}); /* Replace with the correct path */
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .background-bpjs {
            background-image: url({{ asset('images/bg_bpjs_2024.jpg') }});
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .background-fisio {
            background-image: url({{ asset('images/bg_fisio_2024.jpg') }});
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .background-baru {
            background-image: url({{ asset('images/bg_rsck_2023.jpg') }});
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="background-{{$background}}">
    @yield('content')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('bootstrap-3.4.1-dist/js/bootstrap.min.js') }}"></script>
</body>
</html>
