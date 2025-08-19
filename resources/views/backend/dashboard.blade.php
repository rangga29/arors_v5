@extends('backend.layouts.main', ['page_title' => 'DASHBOARD'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">DASHBOARD ({{ env('API_KEY', 'rsck') }})</h4>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-xxl-4 row-cols-lg-3 row-cols-md-2">
            @foreach($dashboardData as $data)
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title text-uppercase fw-bolder mb-2">
                                {{ \Carbon\Carbon::parse($data['date'])->isoFormat('dddd, DD MMMM YYYY') }}
                            </h4>
                            <hr>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-uppercase mb-0">TOTAL PASIEN</h6>
                                <span class="badge bg-primary fs-16">{{ $data['total'] }}</span>
                            </div>

                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Umum</span>
                                <span class="fw-bold">{{ $data['klinikUmum'] }} Pasien</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Asuransi</span>
                                <span class="fw-bold">{{ $data['klinikAsuransi'] }} Pasien</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Baru</span>
                                <span class="fw-bold">{{ $data['klinikBaru'] }} Pasien</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Rehab Medik</span>
                                <span class="fw-bold">{{ $data['totalRehab'] }} Pasien</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Fisioterapi Umum</span>
                                <span class="fw-bold">{{ $data['fisioPagiNonJkn'] }}/{{ $data['fisioPagiNonJknKuota'] }} - {{ $data['fisioSoreNonJkn'] }}/{{ $data['fisioSoreNonJknKuota'] }} Pasien</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Fisioterapi BPJS</span>
                                <span class="fw-bold">{{ $data['fisioPagiJkn'] }}/{{ $data['fisioPagiJknKuota'] }} - {{ $data['fisioSoreJkn'] }}/{{ $data['fisioSoreJknKuota'] }} Pasien</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
