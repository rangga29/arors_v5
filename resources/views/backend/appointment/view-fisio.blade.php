@extends('backend.layouts.main', ['page_title' => 'Data Appointment Fisioterapi - ' . $date])

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
                        <form class="d-flex" method="POST" action="{{ route('appointments.fisioterapi.show.redirect') }}">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control shadow border-0" name="appointment-date" id="appointment-date" data-today="{{ $date_original }}" data-schedule-date-first="{{ $schedule_date_first }}" data-schedule-date-last="{{ $schedule_date_last }}">
                                <span class="input-group-text bg-primary border-primary text-white">
                                    <i class="ri-calendar-todo-fill fs-13"></i>
                                </span>
                            </div>
                            <button type="submit" class="btn btn-primary ms-2"><i class="ri-refresh-line"></i></button>
                        </form>
                    </div>
                    <h4 class="page-title">Data Appointment Fisioterapi - {{ $date }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-justified nav-bordered mb-3">
                        <li class="nav-item">
                            <a href="#umum-pagi" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                UMUM PAGI
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#umum-sore" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                UMUM SORE
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#bpjs-pagi" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                BPJS PAGI
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#bpjs-sore" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                BPJS SORE
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane show active" id="umum-pagi">
                            <table id="basic-datatable-1" class="table table-striped table-bordered table-centered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Token</th>
                                    <th>NORM</th>
                                    <th>Nama</th>
                                    <th>Tanggal Lahir</th>
                                    <th>No. Handphone</th>
                                    <th>Daftar Ulang</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($appointmentDataUmumPagi as $appDataUmumPagi)
                                    <tr>
                                        <td>{{ $appDataUmumPagi['fap_queue'] }}</td>
                                        <td>{{ $appDataUmumPagi['fap_token'] }}</td>
                                        <td>{{ $appDataUmumPagi['fap_norm'] }}</td>
                                        <td>{{ $appDataUmumPagi['fap_name'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appDataUmumPagi['fap_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                        <td>{{ $appDataUmumPagi['fap_phone'] }}</td>
                                        <td>{{ $appDataUmumPagi['fap_appointment_time'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="umum-sore">
                            <table id="basic-datatable-2" class="table table-striped table-bordered table-centered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Token</th>
                                    <th>NORM</th>
                                    <th>Nama</th>
                                    <th>Tanggal Lahir</th>
                                    <th>No. Handphone</th>
                                    <th>Daftar Ulang</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($appointmentDataUmumSore as $appDataUmumSore)
                                    <tr>
                                        <td>{{ $appDataUmumSore['fap_queue'] }}</td>
                                        <td>{{ $appDataUmumSore['fap_token'] }}</td>
                                        <td>{{ $appDataUmumSore['fap_norm'] }}</td>
                                        <td>{{ $appDataUmumSore['fap_name'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appDataUmumSore['fap_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                        <td>{{ $appDataUmumSore['fap_phone'] }}</td>
                                        <td>{{ $appDataUmumSore['fap_appointment_time'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="bpjs-pagi">
                            <table id="basic-datatable-3" class="table table-striped table-bordered table-centered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Token</th>
                                    <th>NORM</th>
                                    <th>Nama</th>
                                    <th>Tanggal Lahir</th>
                                    <th>No. Handphone</th>
                                    <th>Daftar Ulang</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($appointmentDataBpjsPagi as $appDataBpjsPagi)
                                    <tr>
                                        <td>{{ $appDataBpjsPagi['fap_queue'] }}</td>
                                        <td>{{ $appDataBpjsPagi['fap_token'] }}</td>
                                        <td>{{ $appDataBpjsPagi['fap_norm'] }}</td>
                                        <td>{{ $appDataBpjsPagi['fap_name'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appDataBpjsPagi['fap_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                        <td>{{ $appDataBpjsPagi['fap_phone'] }}</td>
                                        <td>{{ $appDataBpjsPagi['fap_appointment_time'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="bpjs-sore">
                            <table id="basic-datatable-4" class="table table-striped table-bordered table-centered dt-responsive nowrap w-100">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Token</th>
                                    <th>NORM</th>
                                    <th>Nama</th>
                                    <th>Tanggal Lahir</th>
                                    <th>No. Handphone</th>
                                    <th>Daftar Ulang</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($appointmentDataBpjsSore as $appDataBpjsSore)
                                    <tr>
                                        <td>{{ $appDataBpjsSore['fap_queue'] }}</td>
                                        <td>{{ $appDataBpjsSore['fap_token'] }}</td>
                                        <td>{{ $appDataBpjsSore['fap_norm'] }}</td>
                                        <td>{{ $appDataBpjsSore['fap_name'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appDataBpjsSore['fap_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                        <td>{{ $appDataBpjsSore['fap_phone'] }}</td>
                                        <td>{{ $appDataBpjsSore['fap_appointment_time'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
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
        'resources/js/customs/appointments.js'
    ])
@endsection
