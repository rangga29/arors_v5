<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="title" content="Registrasi Online RS Cahya Kawaluyan — Daftar Rawat Jalan, Fisioterapi, Rehab Medik">
    <meta name="description"
        content="Registrasi Online Rumah Sakit Cahya Kawaluyan Padalarang. Daftar pasien rawat jalan umum, BPJS, asuransi, fisioterapi, rehabilitasi medik, dan sunday clinic. Cek nomor antrian dan jadwal dokter.">
    <meta name="keywords"
        content="rsck, cahya kawaluyan, rumah sakit, rs cahya kawaluyan, registrasi online, registrasi rsck, registrasi online rsck, daftar rawat jalan, daftar fisioterapi, rehabilitasi medik, sunday clinic, cek antrian rsck, rumah sakit padalarang">
    <meta name="author" content="RS Cahya Kawaluyan">

    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:title"
        content="Registrasi Online RS Cahya Kawaluyan — Daftar Rawat Jalan, Fisioterapi, Rehab Medik">
    <meta property="og:description"
        content="Registrasi Online Rumah Sakit Cahya Kawaluyan Padalarang. Daftar pasien rawat jalan umum, BPJS, asuransi, fisioterapi, rehabilitasi medik, dan sunday clinic.">
    <meta property="og:site_name" content="Registrasi Online RS Cahya Kawaluyan">
    <meta property="og:url" content="{{ url()->current() }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Registrasi Online RS Cahya Kawaluyan">
    <meta name="twitter:description"
        content="Daftar pasien rawat jalan umum, BPJS, asuransi, fisioterapi, rehabilitasi medik, dan sunday clinic di RS Cahya Kawaluyan.">

    <meta name="robots" content="index,follow" />
    <meta name="googlebot" content="index,follow" />
    <meta name="revisit-after" content="2 days" />
    <meta name="author" content="RS Cahya Kawaluyan">
    <meta name="expires" content="never" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <meta name="google-site-verification" content="F9msF55YBBNLSpkRadVhLIbIPl8ronXXjILV-D4yxII" />

    <link rel="shortcut icon" href="{{ asset('images/rsck_trans.png') }}">

    <title>Registrasi Online RS Cahya Kawaluyan — Daftar Rawat Jalan, Fisioterapi, Rehab Medik</title>

    {{-- JSON-LD Structured Data for Google Rich Results --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "MedicalOrganization",
        "name": "RS Cahya Kawaluyan",
        "alternateName": "Rumah Sakit Cahya Kawaluyan",
        "url": "https://registrasi.rscahyakawaluyan.com",
        "logo": "https://registrasi.rscahyakawaluyan.com/images/logo_rsck_new_resize.png",
        "description": "Registrasi Online Rumah Sakit Cahya Kawaluyan Padalarang. Daftar pasien rawat jalan umum, BPJS, asuransi, fisioterapi, rehabilitasi medik, dan sunday clinic.",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Jl. Kota Baru Parahyangan, Kavling Fasilitas Umum",
            "addressLocality": "Padalarang",
            "addressRegion": "Jawa Barat",
            "postalCode": "40553",
            "addressCountry": "ID"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+62-812-1111-8009",
            "contactType": "customer service",
            "availableLanguage": ["Indonesian"]
        },
        "sameAs": [
            "https://www.rscahyakawaluyan.com"
        ]
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Registrasi Online RS Cahya Kawaluyan",
        "url": "https://registrasi.rscahyakawaluyan.com",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://registrasi.rscahyakawaluyan.com/cek-antrian-pasien/norm",
            "query": "required"
        }
    }
    </script>

    @vite(['resources/scss/app.scss', 'resources/scss/icons.scss', 'resources/js/head.js'])
</head>

<body class="bg-home">
    <div class="container home-container">
        {{-- Header --}}
        <header class="home-header">
            <a href="/">
                <img src="{{ asset('images/logo_rsck_new_resize.png') }}" alt="RS Cahya Kawaluyan" class="logo-image">
            </a>
            <h1>Aplikasi Registrasi Online </br>Rumah Sakit</h1>
        </header>

        <main>
            {{-- Section: Pendaftaran Pasien --}}
            <div class="home-section">
                <div class="home-section-title">
                    <i class="ri-user-heart-line"></i> Pendaftaran Pasien
                </div>
                <div class="home-btn-group">
                    <a href="{{ route('umum') }}" class="home-btn home-btn-one">
                        <i class="ri-user-line"></i>
                        <span>Pasien Lama — Umum / Asuransi</span>
                    </a>
                    <a href="{{ route('baru') }}" class="home-btn home-btn-five">
                        <i class="ri-user-add-line"></i>
                        <span>Pasien Baru — Umum / Asuransi</span>
                    </a>
                    <a href="{{ route('rehab-medik-fisioterapi') }}" class="home-btn home-btn-two">
                        <i class="ri-heart-pulse-line"></i>
                        <span>Pasien Klinik Rehabilitasi Medik & Fisioterapi</span>
                    </a>
                    <a href="{{ route('sunday-clinic') }}" class="home-btn home-btn-three">
                        <i class="ri-calendar-event-line"></i>
                        <span>Pasien Sunday Clinic</span>
                    </a>
                </div>
            </div>

            {{-- Section: Informasi Antrian --}}
            <div class="home-section">
                <div class="home-section-title">
                    <i class="ri-list-ordered"></i> Informasi Antrian
                </div>
                <div class="home-btn-group">
                    <a href="{{ route('cek-antrian.norm') }}" class="home-btn home-btn-six">
                        <i class="ri-search-line"></i>
                        <span>Cek Nomor Antrian</span>
                    </a>
                    <a href="{{ route('batal-antrian.norm') }}" class="home-btn home-btn-four">
                        <i class="ri-close-circle-line"></i>
                        <span>Pembatalan Nomor Antrian</span>
                    </a>
                    <a href="https://www.rscahyakawaluyan.com/doctors" class="home-btn home-btn-five" target="_blank">
                        <i class="ri-stethoscope-line"></i>
                        <span>Cek Jadwal Dokter</span>
                    </a>
                </div>
            </div>

            {{-- Section: Layanan BPJS --}}
            <div class="home-section">
                <div class="home-section-title">
                    <i class="ri-shield-check-line"></i> Layanan BPJS
                </div>
                {{-- Pengumuman BPJS --}}
                <div class="home-announcement">
                    <div class="home-announcement-icon">
                        <i class="ri-megaphone-line"></i>
                    </div>
                    <div class="home-announcement-content">
                        Mulai Tanggal <strong>10 Oktober 2024</strong> Pasien BPJS
                        (Kecuali Pasien Rehabilitasi Medik) <strong>WAJIB</strong> Menggunakan Aplikasi Mobile JKN.
                    </div>
                </div>
                <div class="home-btn-group">
                    <a href="https://play.google.com/store/apps/details?id=app.bpjs.mobile&hl=en&gl=US"
                        class="home-btn home-btn-two" target="_blank">
                        <i class="ri-smartphone-line"></i>
                        <span>Layanan Mobile JKN</span>
                    </a>
                    <a href="https://registrasi.rscahyakawaluyan.com/images/tatacara_mobilejkn.jpg"
                        class="home-btn home-btn-two" target="_blank">
                        <i class="ri-file-list-3-line"></i>
                        <span>Tata Cara Mobile JKN</span>
                    </a>
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
