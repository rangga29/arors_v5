@extends('backend.layouts.main', ['page_title' => 'DATA KLINIK'])

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
                    <div class="page-title-right">
                        @can('create clinics')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-modal">
                                <i class="ri-add-fill"></i> Tambah Data
                            </button>
                        @endcan
                    </div>
                    <h4 class="page-title">DATA KLINIK</h4>
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
                                <th>Kode</th>
                                <th>Kode BPJS</th>
                                <th>Nama</th>
                                <th>Umum</th>
                                <th>BPJS</th>
                                <th>Aktif</th>
                                @hasanyrole('administrator|sisfo')
                                    <th>Created By</th>
                                    <th>Updated By</th>
                                @endhasanyrole
                                @if(auth()->user()->can('edit clinics') || auth()->user()->can('delete clinics'))
                                    <th class="text-center">Aksi</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($clinics as $clinic)
                                <tr>
                                    <th scope="row">{{ $clinic->cl_order }}</th>
                                    <td>{{ $clinic->cl_code }}</td>
                                    <td>{{ $clinic->cl_code_bpjs }}</td>
                                    <td>{{ $clinic->cl_name }}</td>
                                    <td>
                                        <span class="fs-20 px-1">
                                            @if($clinic->cl_umum)
                                                <i class="ri-checkbox-circle-fill text-success"></i>
                                            @else
                                                <i class="ri-close-circle-fill text-danger"></i>
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fs-20 px-1">
                                            @if($clinic->cl_bpjs)
                                                <i class="ri-checkbox-circle-fill text-success"></i>
                                            @else
                                                <i class="ri-close-circle-fill text-danger"></i>
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fs-20 px-1">
                                            @if($clinic->cl_active)
                                                <i class="ri-checkbox-circle-fill text-success"></i>
                                            @else
                                                <i class="ri-close-circle-fill text-danger"></i>
                                            @endif
                                        </span>
                                    </td>
                                    @hasanyrole('administrator|sisfo')
                                        <td>{{ $clinic->created_by }}</td>
                                        <td>{{ $clinic->updated_by }}
                                    @endhasanyrole
                                    @if(auth()->user()->can('edit clinics') || auth()->user()->can('delete clinics'))
                                        <td style="max-width: 6px;">
                                            <div class="d-flex align-content-center">
                                                @can('edit clinics')
                                                    <button type="button" class="btn btn-sm btn-warning cl-edit" title="EDIT DATA" data-bs-toggle="modal" data-bs-target="#edit-modal" data-clinic-ucode="{{ $clinic->cl_ucode }}">
                                                        <i class="ri-file-edit-fill"></i>
                                                    </button>
                                                @endcan
                                                @can('delete clinics')
                                                    <form method="POST" action="{{ route('clinics.destroy', $clinic->cl_ucode) }}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger ms-1" title="DELETE DATA" onclick="return confirm('Yakin Ingin Menghapus Data?')">
                                                            <i class="ri-delete-bin-fill"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="add-modal" class="modal modal-lg fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header modal-colored-header bg-primary">
                            <h4 class="modal-title" id="dark-header-modalLabel">Tambah Data Klinik</h4>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('clinics.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="created_by" id="add_created_by" value="{{ auth()->user()->username }}">
                                <div class="mb-3">
                                    <label for="add_cl_code" class="form-label">Kode Klinik</label>
                                    <input type="text" class="form-control" name="cl_code" id="add_cl_code" placeholder="Kode Klinik" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add_cl_code_bpjs" class="form-label">Kode BPJS Klinik</label>
                                    <input type="text" class="form-control" name="cl_code_bpjs" id="add_cl_code_bpjs" placeholder="Kode BPJS Klinik" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add_cl_name" class="form-label">Nama Klinik</label>
                                    <input type="text" class="form-control" name="cl_name" id="add_cl_name" placeholder="Nama Klinik" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add_cl_order" class="form-label">Nomor Urutan</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="cl_order" id="add_cl_order" placeholder="Nomor Urutan" required>
                                        <button type="button" class="btn btn-dark cl-order">Gunakan Nomor Terakhir</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" name="cl_umum" id="add_cl_umum" value="1">
                                        <label for="add_cl_umum" class="form-check-label">Umum</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" name="cl_bpjs" id="add_cl_bpjs" value="1">
                                        <label for="add_cl_bpjs" class="form-check-label">BPJS</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="cl_active" id="add_cl_active_on" value="1" checked>
                                        <label for="add_cl_active_on" class="form-check-label">Aktif</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="cl_active" id="add_cl_active_off" value="0">
                                        <label for="add_cl_active_off" class="form-check-label">Tidak Aktif</label>
                                    </div>
                                </div>
                                <div class="mb-3 text-center">
                                    <button class="btn btn-primary" type="submit">SUBMIT</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div id="edit-modal" class="modal modal-lg fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header modal-colored-header bg-warning">
                            <h4 class="modal-title" id="dark-header-modalLabel">Edit Data Klinik</h4>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="overlay" style="display: none;">
                                <div class="d-flex justify-content-center">
                                    <div class="spinner-border" role="status"></div>
                                </div>
                            </div>
                            <form class="ps-3 pe-3 mt-2 mb-4" action="#" method="POST" id="editForm">
                                @method('PUT')
                                @csrf
                                <input type="hidden" name="updated_by" id="edit_updated_by" value="{{ auth()->user()->username }}">
                                <div class="mb-3">
                                    <label for="edit_cl_code" class="form-label">Kode Klinik</label>
                                    <input type="text" class="form-control" name="cl_code" id="edit_cl_code" placeholder="Kode Klinik" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_cl_code_bpjs" class="form-label">Kode BPJS Klinik</label>
                                    <input type="text" class="form-control" name="cl_code_bpjs" id="edit_cl_code_bpjs" placeholder="Kode BPJS Klinik" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_cl_name" class="form-label">Nama Klinik</label>
                                    <input type="text" class="form-control" name="cl_name" id="edit_cl_name" placeholder="Nama Klinik" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_cl_order" class="form-label">Nomor Urutan</label>
                                    <input type="number" class="form-control" name="cl_order" id="edit_cl_order" placeholder="Nomor Urutan" required>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" name="cl_umum" id="edit_cl_umum" value="1">
                                        <label for="edit_cl_umum" class="form-check-label">Umum</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" name="cl_bpjs" id="edit_cl_bpjs" value="1">
                                        <label for="edit_cl_bpjs" class="form-check-label">BPJS</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="cl_active" id="edit_cl_active_on" value="1">
                                        <label for="edit_cl_active_on" class="form-check-label">Aktif</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="cl_active" id="edit_cl_active_off" value="0">
                                        <label for="edit_cl_active_off" class="form-check-label">Tidak Aktif</label>
                                    </div>
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
        'resources/js/customs/clinics.js'
    ])
@endsection
