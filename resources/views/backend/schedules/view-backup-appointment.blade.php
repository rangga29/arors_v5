@extends('backend.layouts.main', ['page_title' => 'Data Histori Appointment - ' . $date])

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
                    <h4 class="page-title">Data Histori Appointment - {{ $date }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ $schedule['scb_clinic_name'] }} -- {{ $schedule['scb_doctor_name'] }} -- SESI {{ $session }}</h4>
                    <table id="basic-datatable" class="table table-striped table-bordered table-centered dt-responsive nowrap w-100">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Token</th>
                            <th>NORM</th>
                            <th>Nama</th>
                            <th>Tanggal Lahir</th>
                            <th>No. Handphone</th>
                            <th>Tipe</th>
                            <th>Daftar Ulang</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($appointmentData as $appData)
                            <tr>
                                <td>{{ $appData['apb_queue'] }}</td>
                                <td>{{ $appData['apb_token'] }}</td>
                                <td>{{ $appData['apb_norm'] }}</td>
                                <td>{{ $appData['apb_name'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($appData['apb_birthday'])->isoFormat('DD MMMM YYYY') }}</td>
                                <td>{{ $appData['apb_phone'] }}</td>
                                <td>{{ $appData['apb_type'] }}</td>
                                <td>{{ $appData['apb_registration_time'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
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
