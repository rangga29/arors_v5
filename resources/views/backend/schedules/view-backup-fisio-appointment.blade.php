@extends('backend.layouts.main', ['page_title' => 'Data Histori Appointment Fisioterapi - ' . $date])

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
                    </div>
                    <h4 class="page-title">Data Histori Appointment Fisioterapi - {{ $date }}</h4>
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
                                        <td>{{ $appDataUmumPagi['fpb_queue'] }}</td>
                                        <td>{{ $appDataUmumPagi['fpb_token'] }}</td>
                                        <td>{{ $appDataUmumPagi['fpb_norm'] }}</td>
                                        <td>{{ $appDataUmumPagi['fpb_name'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appDataUmumPagi['fpb_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                        <td>{{ $appDataUmumPagi['fpb_phone'] }}</td>
                                        <td>{{ $appDataUmumPagi['fpb_appointment_time'] }}</td>
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
                                        <td>{{ $appDataUmumSore['fpb_queue'] }}</td>
                                        <td>{{ $appDataUmumSore['fpb_token'] }}</td>
                                        <td>{{ $appDataUmumSore['fpb_norm'] }}</td>
                                        <td>{{ $appDataUmumSore['fpb_name'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appDataUmumSore['fpb_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                        <td>{{ $appDataUmumSore['fpb_phone'] }}</td>
                                        <td>{{ $appDataUmumSore['fpb_appointment_time'] }}</td>
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
                                        <td>{{ $appDataBpjsPagi['fpb_queue'] }}</td>
                                        <td>{{ $appDataBpjsPagi['fpb_token'] }}</td>
                                        <td>{{ $appDataBpjsPagi['fpb_norm'] }}</td>
                                        <td>{{ $appDataBpjsPagi['fpb_name'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appDataBpjsPagi['fpb_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                        <td>{{ $appDataBpjsPagi['fpb_phone'] }}</td>
                                        <td>{{ $appDataBpjsPagi['fpb_appointment_time'] }}</td>
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
                                        <td>{{ $appDataBpjsSore['fpb_queue'] }}</td>
                                        <td>{{ $appDataBpjsSore['fpb_token'] }}</td>
                                        <td>{{ $appDataBpjsSore['fpb_norm'] }}</td>
                                        <td>{{ $appDataBpjsSore['fpb_name'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appDataBpjsSore['fpb_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                        <td>{{ $appDataBpjsSore['fpb_phone'] }}</td>
                                        <td>{{ $appDataBpjsSore['fpb_appointment_time'] }}</td>
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
