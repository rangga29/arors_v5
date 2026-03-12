<!DOCTYPE html>
<html>
    <head>
        <title>{{ $title }}</title>
        <style>
            @page {
                margin: 0cm 0cm;
            }

            .watermark {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                opacity: 0.2;
                z-index: -1000;
            }

            .watermark img {
                width: 300px;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }

            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                padding: 0;
                font-family: Arial, sans-serif;
                margin: 0.5cm 1.6cm;
            }

            .content-container {
                text-align: center;
            }

            .centered-image img {
                height: 48px;
                margin-bottom: 20px;
            }

            .title {
                font-size: 28px;
                margin: 0;
            }

            table {
                font-size: xx-small;
                margin: 0;
                width: 100%; /* This is the key change */
            }

            footer {
                position: fixed;
                bottom: 0.5cm;
                left: 0cm;
                right: 1cm;
                text-align: right;
            }

            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <div class="watermark">
            <img src="{{ public_path('images/rsck_logo.png') }}" alt="Watermark">
        </div>
        <div class="content-container">
            <h2 class="title">DATA APPOINTMENT REGISTRASI ONLINE</h2>
            <p class="lead fs-4 fw-bolder border-bottom">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)->isoFormat('dddd, DD MMMM YYYY') }}</p>
        </div>
        <div class="container">
            <table border="1" cellspacing="0" cellpadding="10">
                <thead>
                    <tr>
                        <th>Klinik</th>
                        <th>Dokter</th>
                        <th>Sesi</th>
                        <th>No Antrian</th>
                        <th>Token</th>
                        <th>NORM</th>
                        <th>Nama Pasien</th>
                        <th>Tipe</th>
                        <th>No. Handphone</th>
                        <th>Daftar Ulang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointmentData as $apData)
                        <tr>
                            <td style="text-align: center">{{ $apData['clinic'] }}</td>
                            <td style="text-align: center">{{ $apData['doctor'] }}</td>
                            <td style="text-align: center">{{ $apData['session'] }}</td>
                            <td style="text-align: center">{{ $apData['queue'] }}</td>
                            <td style="text-align: center">{{ $apData['token'] }}</td>
                            <td style="text-align: center">{{ $apData['norm'] }}</td>
                            <td style="text-align: center">{{ $apData['name'] }}</td>
                            <td style="text-align: center">{{ $apData['phone'] }}</td>
                            <td style="text-align: center">{{ $apData['type'] }}</td>
                            <td style="text-align: center">{{ $apData['registration_time'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <footer>
            <p style="font-size: 8px;">CREATED DATE : {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now())->isoFormat('DD MMMM YYYY -- hh:mm:ss') }}</p>
        </footer>

        <div class="page-break"></div>

        <div class="content-container">
            <h2 class="title">DATA APPOINTMENT FISIOTERAPI</h2>
            <p class="lead fs-4 fw-bolder border-bottom">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)->isoFormat('dddd, DD MMMM YYYY') }}</p>
        </div>
        <div class="container">
            <table border="1" cellspacing="0" cellpadding="10">
                <thead>
                <tr>
                    <th>No Antrian</th>
                    <th>NORM</th>
                    <th>Nama Pasien</th>
                    <th>Jenis</th>
                    <th>No. Handphone</th>
                    <th>Daftar Ulang</th>
                </tr>
                </thead>
                <tbody>
                @foreach($fisioData as $fapData)
                    <tr>
                        <td style="text-align: center">{{ $fapData['fap_queue'] }}</td>
                        <td style="text-align: center">{{ $fapData['fap_norm'] }}</td>
                        <td style="text-align: center">{{ $fapData['fap_name'] }}</td>
                        <td style="text-align: center">{{ $fapData['fap_type'] }}</td>
                        <td style="text-align: center">{{ $fapData['fap_phone'] }}</td>
                        <td style="text-align: center">{{ $fapData['fap_appointment_time'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <footer>
            <p style="font-size: 8px;">CREATED DATE : {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now())->isoFormat('DD MMMM YYYY -- hh:mm:ss') }}</p>
        </footer>
    </body>
</html>
