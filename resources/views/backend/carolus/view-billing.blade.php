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
        <div class="card mb-0 bg-3" style="height: auto;">
            <div class="card-shadow">
                <div class="content">
                    <div class="ad-300x50 mb-1">
                        <div class="card bg-1" data-card-height="50"></div>
                    </div>
                </div>

                <div class="content">
                    <h1 class="text-center">BILLING PASIEN RAWAT INAP</h1>
                    <p class="font-18 text-center text-black mt-2 fw-bolder">CAROLUS - KAMAR {{ preg_replace('/ /', ' - ', $qrc_room, 1) }}</p>
                    <div class="divider-icon divider-margins"><i class="fa font-24 color-dark-dark fa-procedures"></i></div>
                </div>

                @foreach($billingData as $billing)
                    <div id="accordionExample" class="content mb-5 bg-fade-dark-dark px-2">
                        <div class="content">
                            <div class="list-group list-custom-small list-icon-0">
                                <a data-bs-toggle="collapse" class="no-effect" href="#collapse-1">
                                    <i class="fa font-16 fa-user color-white"></i>
                                    <span class="font-18 fw-bolder text-white text-uppercase">Informasi Pasien</span>
                                    <i class="fa fa-angle-down color-white"></i>
                                </a>
                            </div>
                            <div id="collapse-1" class="collapse show" data-bs-parent="#accordionExample">
                                <div class="list-group list-custom-small ps-1">
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Nomor RM</div>
                                        <div class="font-14 mx-3">{{ $billing['MedicalNo'] }}</div>
                                    </div>
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Nama Pasien</div>
                                        <div class="font-14 mx-3">{{ $billing['PatientName'] }}</div>
                                    </div>
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Tanggal Lahir</div>
                                        <div class="font-14 mx-3">{{ $billing['DateOfBirth'] }}</div>
                                    </div>
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Tanggal Mulai Dirawat</div>
                                        <div class="font-14 mx-3">{{ $billing['RegistrationDate'] }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group list-custom-small list-icon-0">
                                <a data-bs-toggle="collapse" class="no-effect" href="#collapse-2">
                                    <i class="fa font-16 fa-user-md color-white"></i>
                                    <span class="font-18 fw-bolder text-white text-uppercase">Informasi Dokter</span>
                                    <i class="fa fa-angle-down color-white"></i>
                                </a>
                            </div>
                            <div id="collapse-2" class="collapse" data-bs-parent="#accordionExample">
                                <div class="list-group list-custom-small ps-1">
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Dokter Utama</div>
                                        <div class="font-14 mx-3">{{ $billing['ParamedicName'] }}</div>
                                    </div>
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Dokter</div>
                                        @php
                                            $paramedics = collect($billing['ParamedicTeam'])
                                                ->where('GCParamedicRole', 'X084^002')
                                                ->values(); // reset index
                                        @endphp
                                        @if ($paramedics->count() === 1)
                                            <div class="font-14 mx-3 mb-1">{{ $paramedics[0]['ParamedicName'] }}</div>
                                        @elseif ($paramedics->count() > 1)
                                            @foreach ($paramedics as $paramedic)
                                                <div class="font-14 mx-3 mb-1">• {{ $paramedic['ParamedicName'] }}</div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="list-group list-custom-small list-icon-0">
                                <a data-bs-toggle="collapse" class="no-effect" href="#collapse-3">
                                    <i class="fa font-16 fa-file-invoice-dollar color-white"></i>
                                    <span class="font-18 fw-bolder text-white text-uppercase">Rincian Billing Pasien</span>
                                    <i class="fa fa-angle-down color-white"></i>
                                </a>
                            </div>
                            <div id="collapse-3" class="collapse" data-bs-parent="#accordionExample">
                                <div class="list-group list-custom-small ps-3">
                                    @php
                                        if ($billing['PaymentInformation']['RemainingAmount'] >= 0) {
                                            $uangMuka = $billing['PaymentInformation']['TotalAmount'] - $billing['PaymentInformation']['RemainingAmount'];
                                        } else {
                                            $uangMuka = $billing['PaymentInformation']['TotalAmount'] - abs($billing['PaymentInformation']['RemainingAmount']);
                                        }
                                    @endphp
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Tipe Penanggung</div>
                                        <div class="font-14 mx-3">
                                            {{ $billing['CustomerType'] == 'Pribadi' ? $billing['CustomerType'] : $billing['BusinessPartnerName'] }}
                                        </div>
                                    </div>
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Total Biaya</div>
                                        <div class="font-14 mx-3">Rp {{ number_format($billing['PaymentInformation']['TotalAmount'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">
                                            {{ $billing['CustomerType'] == 'Pribadi' ? 'Uang Muka' : 'Uang Muka / Plafon' }}
                                        </div>
                                        <div class="font-14 mx-3">Rp {{ number_format($uangMuka, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="fw-bolder font-14 text-uppercase">Sisa Biaya</div>
                                        <div class="font-14 mx-3">Rp {{ number_format(abs($billing['PaymentInformation']['RemainingAmount']), 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group list-custom-small list-icon-0">
                                <a data-bs-toggle="collapse" class="no-effect" href="#collapse-4">
                                    <i class="fa font-16 fa-question-circle color-white"></i>
                                    <span class="font-18 fw-bolder text-white text-uppercase">Informasi Lainnya</span>
                                    <i class="fa fa-angle-down color-white"></i>
                                </a>
                            </div>
                            <div id="collapse-4" class="collapse" data-bs-parent="#accordionExample">
                                <div class="list-group list-custom-small ps-2">
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="font-14 mx-1">Informasi billing yang ditampilkan bersifat sementara.</div>
                                    </div>
                                    <div class="text-white mb-3" style="line-height: 1.5;">
                                        <div class="font-14 mx-1">Untuk keterangan lebih lanjut dapat menghubungi petugas FO / kasir pada loket kasir.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <a href="{{ route('carolus.billing-information.logout', ['qrc_room' => $qrc_room, 'qrc_ucode' => $qrc_ucode]) }}" class="back-button btn btn-center-m btn-s rounded-s bg-blue-dark font-700 text-uppercase mb-3">LOGOUT</a>
            </div>
        </div>
        <div class="footer pb-8"></div>
    </div>
</div>
<script type="text/javascript" src="{{ asset('bars/scripts/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('bars/scripts/custom.js') }}"></script>
</body>
