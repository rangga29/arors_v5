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
            opacity: 0.1;
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
            font-size: x-small;
            width: 100%;
            margin: 0;
        }

        footer {
            position: fixed;
            bottom: 0.5cm;
            left: 0cm;
            right: 1cm;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="watermark">
        <img src="{{ public_path('images/rsck_logo.png') }}" alt="Watermark">
    </div>
    <div class="content-container">
        <h2 class="title">JADWAL PRAKTIK DOKTER RAWAT JALAN</h2>
        <p class="lead fs-4 fw-bolder border-bottom">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)->isoFormat('dddd, DD MMMM YYYY') }}</p>
    </div>
    <div class="container">
        <table border="1" cellspacing="0" cellpadding="10">
            <thead>
                <tr>
                    <th>Nama Klinik</th>
                    <th>Nama Dokter</th>
                    <th>Sesi</th>
                    <th>Jam Praktek</th>
                    <th>Umum</th>
                    <th>BPJS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scheduleData as $data)
                    <tr>
                        <td>{{ $data->sc_clinic_name }}</td>
                        <td>{{ $data->sc_doctor_name }}</td>
                        @foreach($data->scheduleDetails as $scheduleDetail)
                            <td style="text-align: center">{{ $scheduleDetail->scd_session }}</td>
                            <td style="text-align: center">{{ \Carbon\Carbon::createFromFormat('H:i:s', $scheduleDetail->scd_start_time)->format('H:i') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $scheduleDetail->scd_end_time)->format('H:i') }}</td>
                            <td style="text-align: center">{{ $scheduleDetail->scd_max_umum }}</td>
                            <td style="text-align: center">{{ $scheduleDetail->scd_max_bpjs }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        <footer>
            <p style="font-size: 8px;">CREATED DATE : {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now())->isoFormat('DD MMMM YYYY -- hh:mm:ss') }}</p>
        </footer>
    </div>
</body>
</html>
