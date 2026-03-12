@extends('backend.layouts.main', ['page_title' => 'Data Jadwal Dokter - Tanggal'])

@section('css')
    @vite([
        'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
        'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css',
        'node_modules/flatpickr/dist/flatpickr.min.css'
    ])
@endsection

@section('content')
    <div id="overlay" style="display: none;">
        <div class="d-flex justify-content-center">
            <div class="spinner-border" role="status"></div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        @can('create schedule dates')
                            <button type="button" class="btn btn-primary sd-add" data-bs-toggle="modal" data-bs-target="#add-modal" data-max-date="{{ \Carbon\Carbon::create($schedule_date_last)->addDay(1) }}">
                                <i class="ri-add-fill"></i> Tambah Data
                            </button>
                        @endcan
                    </div>
                    <h4 class="page-title">Data Jadwal Dokter - Tanggal</h4>
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
                                {{--@hasanyrole('administrator|sisfo')--}}
                                    {{--<th>Created By</th>--}}
                                    {{--<th>Updated By</th>--}}
                                {{--@endhasanyrole--}}
                                <th class="text-center">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($schedule_dates as $schedule_date)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    <td>{{ \Carbon\Carbon::parse($schedule_date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}</td>
                                    <td>
                                    <span class="fs-20 px-1">
                                        @if($schedule_date->sd_is_downloaded)
                                            <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                            <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                    </span>
                                    </td>
                                    <td>
                                    <span class="fs-20 px-1">
                                        @if($schedule_date->sd_is_holiday)
                                            <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                            <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                     </span>
                                    </td>
                                    <td>{{ $schedule_date->sd_holiday_desc }}</td>
                                    {{--@hasanyrole('administrator|sisfo')--}}
                                        {{--<td>{{ $schedule_date->created_by }}</td>--}}
                                        {{--<td>{{ $schedule_date->updated_by }}</td>--}}
                                    {{--@endhasanyrole--}}
                                    <td style="max-width: 120px">
                                        <div class="d-flex justify-content-center">
                                            @can('view schedules')
                                                <a href="{{ route('schedules', $schedule_date->sd_date) }}" class="btn btn-sm btn-primary ms-2" title="LIHAT JADWAL" {{ $schedule_date->sd_is_downloaded ? '' : 'disabled' }}>
                                                    <i class="ri-eye-fill"></i>
                                                </a>
                                            @endcan
                                            @can('download schedules')
                                                <form method="GET" action="{{ route('schedules.dates.download', $schedule_date->sd_ucode) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success ms-2 sd-download" title="DOWNLOAD JADWAL" {{ $schedule_date->sd_is_holiday || $schedule_date->sd_is_downloaded ? 'disabled' : '' }}>
                                                        <i class="ri-download-2-fill"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('update schedules')
                                                <form method="GET" action="{{ route('schedules.dates.downloadUpdate', $schedule_date->sd_ucode) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning ms-2 sd-download" title="DOWNLOAD UPDATE JADWAL" {{ $schedule_date->sd_is_downloaded ? '' : 'disabled' }}>
                                                        <i class="ri-refresh-line"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('delete schedules')
                                                <form method="POST" action="{{ route('schedules.dates.destroy', $schedule_date->sd_ucode) }}">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger ms-2" title="HAPUS JADWAL" onclick="return confirm('Yakin Ingin Menghapus Jadwal Pada Tanggal Ini?')" {{ $schedule_date->sd_is_downloaded ? '' : 'disabled' }}>
                                                        <i class="ri-delete-bin-fill"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('edit schedule dates')
                                                <button type="button" class="btn btn-sm btn-info ms-2 sd-edit" title="EDIT TANGGAL" data-bs-toggle="modal" data-bs-target="#edit-modal" data-schedule-date-ucode="{{ $schedule_date->sd_ucode }}" {{ $schedule_date->sd_is_downloaded ? 'disabled' : '' }}>
                                                    <i class="ri-edit-fill"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="add-modal" class="modal modal-lg  fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header modal-colored-header bg-primary">
                            <h4 class="modal-title" id="dark-header-modalLabel">Tambah Data Tanggal</h4>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('schedules.dates.store') }}" method="POST">
                                <h5 class="mb-3">Data Tanggal Saat Ini : {{ \Carbon\Carbon::parse($schedule_date_first)->isoFormat('DD MMMM YYYY') }} - {{ \Carbon\Carbon::parse($schedule_date_last)->isoFormat('DD MMMM YYYY') }}</h5>
                                @csrf
                                <input type="hidden" name="created_by" id="add_created_by" value="{{ auth()->user()->username }}">
                                <div class="mb-3">
                                    <label for="download_date" class="form-label">Download Sampai Tanggal</label>
                                    <input type="text" class="form-control" name="download_date" id="download_date" placeholder="Download Sampai Tanggal">
                                </div>
                                <div class="mb-3 text-center">
                                    <button class="btn btn-primary" type="submit">SUBMIT</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div id="edit-modal" class="modal modal-lg  fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header modal-colored-header bg-warning">
                            <h4 class="modal-title" id="dark-header-modalLabel">Edit Data Tanggal</h4>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('schedules.dates.update', ['scheduleDate' => 'SD_UCODE']) }}" method="POST" id="editForm">
                                @method('PUT')
                                @csrf
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="sd_is_holiday" id="edit_sd_holiday_off" value="0">
                                        <label for="edit_sd_holiday_on" class="form-check-label">Hari Biasa</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="sd_is_holiday" id="edit_sd_holiday_on" value="1">
                                        <label for="edit_sd_holiday_off" class="form-check-label">Hari Libur</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_sd_holiday_desc" class="form-label">Deskripsi Hari Libur</label>
                                    <input type="text" class="form-control" name="sd_holiday_desc" id="edit_sd_holiday_desc" placeholder="Deskripsi Hari Libur">
                                </div>
                                <div class="mb-3 text-center">
                                    <button class="btn btn-primary" type="submit">SUBMIT</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite([
        'resources/js/customs/datatable.js',
        'resources/js/customs/schedule-dates.js'
    ])
@endsection
