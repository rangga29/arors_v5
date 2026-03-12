<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            width: 280px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0.8cm 1cm 1cm 1cm;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
        }

        .print-header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .print-header img {
            height: 35px;
            margin-bottom: 4px;
        }

        .print-header h1 {
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 2px 0;
        }

        .print-header .subtitle {
            font-size: 11px;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .highlight-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .highlight-item {
            flex: 1;
            text-align: center;
            border: 1.5px solid #2c3e50;
            border-radius: 6px;
            padding: 6px 8px;
        }

        .highlight-item:first-child {
            margin-left: 0;
        }

        .highlight-item:last-child {
            margin-right: 0;
        }

        .highlight-item .hl-label {
            font-size: 8px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .highlight-item .hl-value {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 1px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .data-table tr {
            border-bottom: 1px solid #e8e8e8;
        }

        .data-table tr:last-child {
            border-bottom: none;
        }

        .data-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .data-table .dt-label {
            width: 42%;
            font-weight: 600;
            color: #555;
            font-size: 10.5px;
        }

        .data-table .dt-sep {
            width: 3%;
            color: #999;
            font-size: 10.5px;
        }

        .data-table .dt-value {
            width: 55%;
            color: #222;
            font-size: 10.5px;
        }

        .section-title {
            font-size: 9px;
            font-weight: 700;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            margin-bottom: 6px;
            margin-top: 10px;
        }

        .notes-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 6px 10px;
            margin-top: 10px;
        }

        .notes-box .notes-title {
            font-size: 8px;
            font-weight: 700;
            color: #dc3545;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .notes-box p {
            font-size: 9px;
            color: #555;
            margin-bottom: 2px;
            text-align: justify;
            line-height: 1.3;
        }

        footer {
            position: fixed;
            bottom: 0.4cm;
            left: 1cm;
            right: 1cm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #ddd;
            padding-top: 3px;
        }

        footer .footer-left,
        footer .footer-right {
            font-size: 7px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="watermark"><img src="{{ public_path('images/rsck_logo.png') }}" alt="Watermark"></div>
    <div class="print-header">
        <img src="{{ public_path('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan">
        <h1>REGISTRASI FISIOTERAPI UMUM</h1>
        <div class="subtitle">Bukti Pendaftaran</div>
    </div>
    <div class="highlight-box">
        <div class="highlight-item">
            <div class="hl-label">Kode Appointment</div>
            <div class="hl-value">{{ $appointmentData['ap_no'] }}</div>
        </div>
        <div class="highlight-item">
            <div class="hl-label">No Antrian</div>
            <div class="hl-value">{{ $appointmentData['ap_queue'] }}</div>
        </div>
        <div class="highlight-item">
            <div class="hl-label">Waktu Daftar Ulang</div>
            <div class="hl-value">
                {{ \Carbon\Carbon::createFromFormat('H:i:s', $appointmentData['ap_registration_time'])->format('H:i') }}
                WIB</div>
        </div>
    </div>
    <div class="section-title">Data Pasien</div>
    <table class="data-table">
        <tr>
            <td class="dt-label">NORM</td>
            <td class="dt-sep">:</td>
            <td class="dt-value">{{ $appointmentDetailData['uap_norm'] }}</td>
        </tr>
        <tr>
            <td class="dt-label">Nama Pasien</td>
            <td class="dt-sep">:</td>
            <td class="dt-value">{{ $appointmentDetailData['uap_name'] }}</td>
        </tr>
        <tr>
            <td class="dt-label">Tanggal Lahir</td>
            <td class="dt-sep">:</td>
            <td class="dt-value">
                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $appointmentDetailData['uap_birthday'])->isoFormat('DD MMMM YYYY') }}
            </td>
        </tr>
        <tr>
            <td class="dt-label">No. Handphone</td>
            <td class="dt-sep">:</td>
            <td class="dt-value">{{ $appointmentDetailData['uap_phone'] }}</td>
        </tr>
    </table>
    <div class="section-title">Detail Pendaftaran</div>
    <table class="data-table">
        <tr>
            <td class="dt-label">Tanggal Pendaftaran</td>
            <td class="dt-sep">:</td>
            <td class="dt-value">
                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $scheduleDateData['sd_date'])->isoFormat('dddd, DD MMMM YYYY') }}
            </td>
        </tr>
        <tr>
            <td class="dt-label">Nama Klinik</td>
            <td class="dt-sep">:</td>
            <td class="dt-value">{{ $scheduleData['sc_clinic_name'] }}</td>
        </tr>
        <tr>
            <td class="dt-label">Nama Petugas</td>
            <td class="dt-sep">:</td>
            <td class="dt-value">{{ $scheduleData['sc_doctor_name'] }}</td>
        </tr>
        <tr>
            <td class="dt-label">Sesi</td>
            <td class="dt-sep">:</td>
            <td class="dt-value">SESI {{ $scheduleDetailData['scd_session'] }}</td>
        </tr>
    </table>
    {{-- <div class="qr-section">
        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
    </div> --}}
    <div class="notes-box">
        <div class="notes-title">Catatan Penting</div>
        <p>* Untuk konfirmasi kepastian jadwal praktek petugas / terdapat pertanyaan dapat menghubungi Customer Service
            di nomor WhatsApp <strong>0812 1111 8009</strong>.</p>
    </div>
    <footer>
        <div class="footer-left">RS Cahya Kawaluyan — Registrasi Online</div>
        <div class="footer-right">Dicetak:
            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $appointmentData['created_at'])->isoFormat('DD MMMM YYYY — HH:mm:ss') }}
        </div>
    </footer>
</body>

</html>
