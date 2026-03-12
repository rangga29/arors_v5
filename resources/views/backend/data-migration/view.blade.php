@extends('backend.layouts.main', ['page_title' => 'Data Migration'])

@section('css')
    @vite(['node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">DATA MIGRATION</h4>
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                        <strong>SUCCESS - </strong>{{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                        <strong>ERROR : </strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
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
                        <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('data-migration.export') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="selectedType" class="form-label control-label">Tipe Data Pasien</label>
                                <select class="form-control form-control-lg shadow border-0" name="selectedType"
                                    id="selectedType" required>
                                    <option value="">Pilih Tipe Data Pasien</option>
                                    <option value="umum">Pasien Umum</option>
                                    <option value="asuransi">Pasien Asuransi</option>
                                    <option value="bpjs">Pasien BPJS</option>
                                    <option value="baru">Pasien Baru</option>
                                    <option value="fisioterapi">Pasien Fisioterapi</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="add_csv_file" class="form-label control-label">File CSV</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" name="csv_file" id="add_csv_file"
                                        placeholder="File CSV" required>
                                </div>
                            </div>
                            <div class="alert alert-info mb-3" style="font-size: 13px;">
                                <i class="ri-information-line"></i>
                                <strong>Format Kolom CSV:</strong><br>
                                <b>Kolom wajib (semua tipe):</b>
                                <code>sd_date</code>, <code>sc_doctor_code</code>, <code>sc_clinic_code</code>,
                                <code>scd_session</code>, <code>ap_ucode</code>, <code>ap_no</code>,
                                <code>ap_token</code>, <code>ap_queue</code>, <code>ap_type</code>,
                                <code>ap_registration_time</code>, <code>ap_appointment_time</code><br><br>
                                <b>Kolom tambahan per tipe:</b><br>
                                • <b>Umum:</b> <code>uap_norm</code>, <code>uap_name</code>,
                                <code>uap_birthday</code> (dd/mm/yyyy), <code>uap_gender</code>,
                                <code>uap_phone</code><br>
                                • <b>Asuransi:</b> <code>aap_norm</code>, <code>aap_name</code>,
                                <code>aap_birthday</code> (dd/mm/yyyy), <code>aap_gender</code>,
                                <code>aap_phone</code>, <code>aap_business_partner_code</code>,
                                <code>aap_business_partner</code><br>
                                • <b>BPJS:</b> <code>bap_norm</code>, <code>bap_name</code>,
                                <code>bap_birthday</code> (dd/mm/yyyy), <code>bap_gender</code>,
                                <code>bap_phone</code>, <code>bap_bpjs</code>, <code>bap_ppk1</code><br>
                                • <b>Baru:</b> <code>nap_name</code>, <code>nap_birthday</code> (dd/mm/yyyy),
                                <code>nap_phone</code>, <code>nap_ssn</code>, <code>nap_gender</code>,
                                <code>nap_address</code>, <code>nap_email</code>,
                                <code>nap_business_partner_code</code>, <code>nap_business_partner_name</code><br>
                                • <b>Fisioterapi:</b> <code>sd_date</code>, <code>fap_type</code>, <code>fap_norm</code>,
                                <code>fap_name</code>, <code>fap_birthday</code> (yyyy-mm-dd), <code>fap_gender</code>,
                                <code>fap_phone</code>, <code>fap_bpjs</code>, <code>fap_registration_time</code>,
                                <code>fap_appointment_time</code>
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
    @vite(['resources/js/customs/datatable.js'])
@endsection
