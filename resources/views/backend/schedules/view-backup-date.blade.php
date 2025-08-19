@extends('backend.layouts.main', ['page_title' => 'Data Histori Jadwal Dokter - Tanggal'])

@section('css')
    @vite([
        'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
        'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css',
        'node_modules/flatpickr/dist/flatpickr.min.css'
    ])
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">Data Histori Jadwal Dokter - Tanggal</h4>
                </div>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="alert" aria-label="Close"></button>
                        <strong>SUCCESS - </strong>{{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="alert" aria-label="Close"></button>
                        <strong>ERROR : </strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="basic-datatable" class="table table-striped table-bordered table-centered dt-responsive nowrap w-100">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Download</th>
                            <th>Libur</th>
                            <th>Desc Libur</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($schedule_dates as $schedule_date)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ \Carbon\Carbon::parse($schedule_date->sdb_date)->isoFormat('DD MMMM YYYY') }}</td>
                                <td>
                                    <span class="fs-20 px-1">
                                        @if($schedule_date->sdb_is_downloaded)
                                            <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                            <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="fs-20 px-1">
                                        @if($schedule_date->sdb_is_holiday)
                                            <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                            <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                     </span>
                                </td>
                                <td>{{ $schedule_date->sdb_holiday_desc }}</td>
                                <td style="max-width: 120px">
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ route('schedules.backup', $schedule_date->sdb_date) }}" class="btn btn-sm btn-primary ms-2 {{ $schedule_date->sdb_is_downloaded ? '' : 'disabled' }}" title="LIHAT JADWAL">
                                            <i class="ri-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('schedules.backup.fisio', $schedule_date->sdb_date) }}" class="btn btn-sm btn-secondary ms-2 {{ $schedule_date->sdb_is_downloaded ? '' : 'disabled' }}" title="LIHAT APPOINTMENT FISIO">
                                            <i class="ri-eye-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection

@section('script')
    @vite(['resources/js/customs/datatable.js'])
@endsection
