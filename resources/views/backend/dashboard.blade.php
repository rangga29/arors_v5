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

        @hasanyrole('administrator|sisfo')
            {{-- Maintenance Mode Toggle --}}
            @php
                $maintenanceFile = storage_path('app/maintenance.json');
                $maintenanceData = file_exists($maintenanceFile)
                    ? json_decode(file_get_contents($maintenanceFile), true)
                    : null;
                $isMaintenanceActive = $maintenanceData['enabled'] ?? false;
            @endphp
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border {{ $isMaintenanceActive ? 'border-danger' : 'border-success' }}">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i
                                        class="ri-settings-3-line fs-4 {{ $isMaintenanceActive ? 'text-danger' : 'text-success' }}"></i>
                                    <div>
                                        <span class="fw-bold">Mode Maintenance</span>
                                        @if ($isMaintenanceActive)
                                            <span class="badge bg-danger ms-2">AKTIF</span>
                                            <div class="text-muted small">Frontend tidak bisa diakses oleh user</div>
                                        @else
                                            <span class="badge bg-success ms-2">NONAKTIF</span>
                                            <div class="text-muted small">Frontend berjalan normal</div>
                                        @endif
                                    </div>
                                </div>
                                <form action="{{ route('maintenance.toggle') }}" method="POST"
                                    class="d-flex align-items-center gap-2">
                                    @csrf
                                    @if (!$isMaintenanceActive)
                                        <input type="text" name="message" class="form-control form-control-sm"
                                            placeholder="Pesan maintenance (opsional)" style="width: 250px;">
                                    @endif
                                    <button type="submit"
                                        class="btn btn-sm {{ $isMaintenanceActive ? 'btn-success' : 'btn-danger' }}"
                                        onclick="return confirm('{{ $isMaintenanceActive ? 'Nonaktifkan mode maintenance? Frontend akan kembali normal.' : 'Aktifkan mode maintenance? Frontend tidak akan bisa diakses user.' }}')">
                                        <i
                                            class="ri-{{ $isMaintenanceActive ? 'play-circle-line' : 'pause-circle-line' }} me-1"></i>
                                        {{ $isMaintenanceActive ? 'Nonaktifkan' : 'Aktifkan' }} Maintenance
                                    </button>
                                </form>
                            </div>
                            @if (session('success'))
                                <div class="alert alert-info alert-dismissible fade show mt-2 mb-0 py-1 px-2 small"
                                    role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endhasanyrole

        <div class="row row-cols-1 row-cols-xxl-4 row-cols-lg-3 row-cols-md-2">
            @foreach ($dashboardData as $data)
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title text-uppercase fw-bolder mb-2">
                                {{ \Carbon\Carbon::parse($data['date'])->isoFormat('dddd, DD MMMM YYYY') }}
                            </h4>
                            <hr class="my-2">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-uppercase mb-0">TOTAL PASIEN</h6>
                                <span class="badge bg-primary fs-16">{{ $data['total'] }}</span>
                            </div>

                            @if ($data['isSunday'])
                                <table class="table table-sm table-borderless mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-info fw-bold ps-0" style="font-size: 12px;">
                                                <i class="ri-sun-line"></i> Sunday Clinic
                                            </th>
                                            <th class="text-end pe-0" style="font-size: 11px; width: 60px;">Pasien</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['scUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['scAsuransi'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Baru Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['scBaruUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Baru Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['scBaruAsuransi'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @else
                                <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                                    {{-- RAWAT JALAN --}}
                                    <thead>
                                        <tr>
                                            <th class="text-primary fw-bold ps-0 pb-0" style="font-size: 12px;">
                                                <i class="ri-stethoscope-line"></i> Rawat Jalan
                                            </th>
                                            <th class="text-end pe-0 pb-0" style="font-size: 11px; width: 60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rjUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rjAsuransi'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Baru Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rjBaruUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Baru Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rjBaruAsuransi'] }}</td>
                                        </tr>
                                    </tbody>

                                    {{-- REHAB MEDIK --}}
                                    <thead>
                                        <tr>
                                            <th class="text-success fw-bold ps-0 pb-0 pt-2" style="font-size: 12px;">
                                                <i class="ri-heart-pulse-line"></i> Rehabilitasi Medik
                                            </th>
                                            <th class="pe-0 pb-0 pt-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rmUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rmAsuransi'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">BPJS</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rmBpjs'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Baru Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rmBaruUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Baru Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['rmBaruAsuransi'] }}</td>
                                        </tr>
                                    </tbody>

                                    {{-- FISIOTERAPI --}}
                                    <thead>
                                        <tr>
                                            <th class="text-warning fw-bold ps-0 pb-0 pt-2" style="font-size: 12px;">
                                                <i class="ri-walk-line"></i> Fisioterapi
                                            </th>
                                            <th class="pe-0 pb-0 pt-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['fisioUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['fisioAsuransi'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">BPJS</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['fisioBpjs'] }}</td>
                                        </tr>
                                    </tbody>

                                    {{-- TERAPI WICARA (HIDDEN)
                                    <thead>
                                        <tr>
                                            <th class="text-danger fw-bold ps-0 pb-0 pt-2" style="font-size: 12px;">
                                                <i class="ri-chat-voice-line"></i> Terapi Wicara
                                            </th>
                                            <th class="pe-0 pb-0 pt-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['twUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['twAsuransi'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">BPJS</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['twBpjs'] }}</td>
                                        </tr>
                                    </tbody>

                                    -- TERAPI OKUPASI (HIDDEN)
                                    <thead>
                                        <tr>
                                            <th class="text-secondary fw-bold ps-0 pb-0 pt-2" style="font-size: 12px;">
                                                <i class="ri-hand-heart-line"></i> Terapi Okupasi
                                            </th>
                                            <th class="pe-0 pb-0 pt-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Umum</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['toUmum'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">Asuransi</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['toAsuransi'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-2 py-0">BPJS</td>
                                            <td class="fw-bold text-end pe-0 py-0">{{ $data['toBpjs'] }}</td>
                                        </tr>
                                    </tbody> --}}
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
