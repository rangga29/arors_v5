@extends('backend.layouts.main', ['page_title' => 'Data Jadwal Dokter - ' . $date])

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
                    <div class="page-title-right">
                        <div class="d-flex">
                            <form class="input-group input-group-custom" method="POST" action="{{ route('schedules.dates.show.redirect') }}">
                                @csrf
                                <input type="text" class="form-control shadow border-0" name="schedule-date" id="schedule-date" data-today="{{ $date_original }}" data-schedule-date-first="{{ $schedule_date_first }}" data-schedule-date-last="{{ $schedule_date_last }}">
                                <span class="input-group-text bg-primary border-primary text-white">
                                    <i class="ri-calendar-todo-fill fs-13"></i>
                                </span>
                                <button type="submit" class="btn btn-primary ms-2"><i class="ri-refresh-line"></i></button>
                            </form>
                            <a href="{{ route('schedule.print', $date_original) }}" class="btn btn-info ms-2" title="PRINT JADWAL"><i class="ri-printer-line"></i></a>
                        </div>
                    </div>
                    <h4 class="page-title">Data Jadwal Dokter - {{ $date }}</h4>
                </div>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="alert" aria-label="Close"></button>
                        <strong>SUCCESS - </strong>{{ session('success') }}
                    </div>
                @endif
                @if(session('danger'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="alert" aria-label="Close"></button>
                        <strong>ERROR : </strong>{{ session('danger') }}
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
                            <th rowspan="2" class="align-middle text-center">No</th>
                            <th colspan="2" class="text-center">Klinik</th>
                            <th colspan="2" class="text-center">Dokter</th>
                            <th rowspan="2" class="align-middle text-center">Sesi</th>
                            <th colspan="2" class="text-center">Operational Time</th>
                            <th colspan="2" class="text-center">Pasien Umum</th>
                            <th colspan="2" class="text-center">Pasien BPJS</th>
                            <th rowspan="2" class="align-middle text-center"></th>
                            {{--@hasanyrole('administrator|sisfo')--}}
                                {{--<th rowspan="2" class="align-middle text-center">CR</th>--}}
                                {{--<th rowspan="2" class="align-middle text-center">UP</th>--}}
                            {{--@endhasanyrole--}}
                            @can('update schedules')
                                <th rowspan="2" class="align-middle text-center">Aksi</th>
                            @endcan
                        </tr>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Used</th>
                            <th>Max</th>
                            <th>Used</th>
                            <th>Max</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($schedules as $schedule)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ $schedule->sc_clinic_code }}</td>
                                <td>{{ $schedule->sc_clinic_name }}</td>
                                <td>{{ $schedule->sc_doctor_code }}</td>
                                <td>{{ $schedule->sc_doctor_name }}</td>
                                @foreach($schedule->scheduleDetails as $scheduleDetail)
                                    <td class="text-center">{{ $scheduleDetail->scd_session }}</td>
                                    <td>{{ $scheduleDetail->scd_start_time }}</td>
                                    <td>{{ $scheduleDetail->scd_end_time }}</td>
                                    <td class="fw-bolder text-center {{ $scheduleDetail->scd_umum == 0 ? 'text-danger' : '' }}">{{ $scheduleDetail->scd_counter_online_umum }}</td>
                                    <td class="fw-bolder text-center {{ $scheduleDetail->scd_umum == 0 ? 'text-danger' : '' }}">{{ $scheduleDetail->scd_online_umum }}</td>
                                    <td class="fw-bolder text-center {{ $scheduleDetail->scd_bpjs == 0 ? 'text-danger' : '' }}">{{ $scheduleDetail->scd_counter_online_bpjs }}</td>
                                    <td class="fw-bolder text-center {{ $scheduleDetail->scd_bpjs == 0 ? 'text-danger' : '' }}">{{ $scheduleDetail->scd_online_bpjs }}</td>
                                @endforeach
                                <td>
                                    <span class="fs-20">
                                        @if($schedule->sc_available)
                                            <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                            <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                    </span>
                                </td>
                                {{--@hasanyrole('administrator|sisfo')--}}
                                    {{--<td>{{ $schedule->created_by }}</td>--}}
                                    {{--<td>{{ $schedule->updated_by }}</td>--}}
                                {{--@endhasanyrole--}}
                                @can('update schedules')
                                    <td style="max-width: 160px">
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('appointments.doctor', [$date_original, $schedule->sc_clinic_code, $schedule->sc_doctor_code, $scheduleDetail->scd_session]) }}" class="btn btn-sm btn-primary" title="APPOINTMENT">
                                                <i class="ri-eye-fill"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-secondary cl-quota ms-1" title="EDIT KUOTA" data-bs-toggle="modal" data-bs-target="#change-quota-modal" data-schedule-date="{{ $date_original }}" data-schedule-ucode="{{ $schedule->sc_ucode }}">
                                                <i class="ri-pencil-fill"></i>
                                            </button>
                                            <form method="POST" action="{{ route('schedule.available', [$date_original, $schedule->sc_ucode]) }}" class="ms-1" id="editForm">
                                                @csrf
                                                @if($schedule->sc_available)
                                                    <button type="submit" class="btn btn-sm btn-danger" title="NON AKTIFKAN">
                                                        <i class="ri-download-fill"></i>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-success" title="AKTIFKAN">
                                                        <i class="ri-upload-fill"></i>
                                                    </button>
                                                @endif
                                            </form>
                                            <form method="GET" action="{{ route('schedule.update', [$date_original, $schedule->sc_ucode, $scheduleDetail->scd_session]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning ms-1 sd-download" title="UPDATE JADWAL">
                                                    <i class="ri-refresh-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="change-quota-modal" class="modal modal-lg fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-colored-header bg-primary">
                    <h4 class="modal-title" id="dark-header-modalLabel">Ubah Kuota Online</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="overlay" style="display: none;">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border" role="status"></div>
                        </div>
                    </div>
                    <form class="ps-3 pe-3 mt-2 mb-4" action="#" method="POST" id="editQuota">
                        @method('PUT')
                        @csrf
                        <input type="hidden" name="updated_by" id="edit_updated_by" value="{{ auth()->user()->username }}">
                        <div class="mb-3">
                            <label for="edit_scd_online_umum" class="form-label">Kuota Pasien Umum</label>
                            <input type="number" class="form-control" name="scd_online_umum" id="edit_scd_online_umum" placeholder="Kuota Pasien Umum" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_scd_online_bpjs" class="form-label">Kuota Pasien BPJS</label>
                            <input type="number" class="form-control" name="scd_online_bpjs" id="edit_scd_online_bpjs" placeholder="Kuota Pasien BPJS" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" name="scd_umum" id="edit_scd_umum" value="1">
                                <label for="edit_scd_umum" class="form-check-label">Umum</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" name="scd_bpjs" id="edit_scd_bpjs" value="1">
                                <label for="edit_scd_bpjs" class="form-check-label">BPJS</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">* Gunakan hanya jika kuota di Medinfras tidak bisa diubah atau kuota berubah mendadak.</label>
                            <label class="form-label">* Jika perubahan kuota bersifat permanent harus di ubah di Medinfras.</label>
                        </div>
                        <div class="mb-3 text-center">
                            <button class="btn btn-primary" type="submit">SUBMIT</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite([
        'resources/js/customs/datatable.js',
        'resources/js/customs/schedules.js'
    ])
@endsection

