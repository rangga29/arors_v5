@extends('backend.layouts.main', ['page_title' => 'Data Migration'])

@section('css')
    @vite([
        'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
        'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'
    ])
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">DATA MIGRATION</h4>
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
                        <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('data-migration.export') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="selectedScheduleDate" class="form-label control-label">Tanggal</label>
                                <select class="form-control form-control-lg shadow border-0" name="selectedScheduleDate" id="selectedScheduleDate" required>
                                    <option value="">Pilih Tanggal</option>
                                    @foreach($schedule_dates as $schedule_date)
                                        <option value="{{ $schedule_date->sd_ucode }}">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m-d', $schedule_date->sd_date)->isoFormat('dddd, DD MMMM YYYY') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="add_csv_file" class="form-label control-label">File CSV</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" name="csv_file" id="add_csv_file" placeholder="File CSV" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="selectedType" class="form-label control-label">Tipe Data Pasien</label>
                                <select class="form-control form-control-lg shadow border-0" name="selectedType" id="selectedType" required>
                                    <option value="">Pilih Tipe Data Pasien</option>
                                    <option value="umum">Pasien Umum</option>
                                    <option value="asuransi">Pasien Asuransi</option>
                                    <option value="bpjs">Pasien BPJS</option>
                                    <option value="fisio">Pasien Fisioterapi</option>
                                    <option value="baru">Pasien Baru</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary" type="submit">SUBMIT</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite([
        'resources/js/customs/datatable.js',
    ])
@endsection
