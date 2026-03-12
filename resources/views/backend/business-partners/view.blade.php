@extends('backend.layouts.main', ['page_title' => 'DATA ASURANSI'])

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
                        @can('create business partners')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-modal">
                                <i class="ri-add-fill"></i> Tambah Data
                            </button>
                        @endcan
                    </div>
                    <h4 class="page-title">DATA ASURANSI</h4>
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
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Skema</th>
                                <th>Kontrak</th>
                                <th>Aktif</th>
                                @hasanyrole('administrator|sisfo')
                                    <th>Created By</th>
                                    <th>Updated By</th>
                                @endhasanyrole
                                @if(auth()->user()->can('edit business partners') || auth()->user()->can('delete business partners'))
                                    <th class="text-center">Aksi</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($partners as $partner)
                                <tr>
                                    <th scope="row">{{ $partner->bp_order }}</th>
                                    <td>{{ $partner->bp_code }}</td>
                                    <td>{{ $partner->bp_name }}</td>
                                    <td>{{ $partner->bp_type }}</td>
                                    <td>{{ $partner->bp_scheme }}</td>
                                    <td>{{ $partner->bp_contract }}</td>
                                    <td>
                                        <span class="fs-20 px-1">
                                            @if($partner->bp_active)
                                                <i class="ri-checkbox-circle-fill text-success"></i>
                                            @else
                                                <i class="ri-close-circle-fill text-danger"></i>
                                            @endif
                                        </span>
                                    </td>
                                    @hasanyrole('administrator|sisfo')
                                        <td>{{ $partner->created_by }}</td>
                                        <td>{{ $partner->updated_by }}</td>
                                    @endhasanyrole
                                    @if(auth()->user()->can('edit business partners') || auth()->user()->can('delete business partners'))
                                        <td>
                                            <div class="d-flex align-content-center">
                                                @can('edit business partners')
                                                    <button type="button" class="btn btn-sm btn-warning ms-2 bp-edit" title="EDIT DATA" data-bs-toggle="modal" data-bs-target="#edit-modal" data-partner-ucode="{{ $partner->bp_ucode }}">
                                                        <i class="ri-file-edit-fill"></i>
                                                    </button>
                                                @endcan
                                                @can('delete business partners')
                                                    <form method="POST" action="{{ route('businessPartners.destroy', $partner->bp_ucode) }}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger ms-2" title="DELETE DATA" onclick="return confirm('Yakin Ingin Menghapus Data?')">
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
                            <h4 class="modal-title" id="dark-header-modalLabel">Tambah Data Asuransi</h4>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('businessPartners.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="created_by" id="add_created_by" value="{{ auth()->user()->username }}">
                                <div class="mb-3">
                                    <label for="add_bp_code" class="form-label">Kode Asuransi</label>
                                    <input type="text" class="form-control" name="bp_code" id="add_bp_code" placeholder="Kode Asuransi" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add_bp_name" class="form-label">Nama Asuransi</label>
                                    <input type="text" class="form-control" name="bp_name" id="add_bp_name" placeholder="Nama Asuransi" required>
                                </div>
                                <div class="mb-3">
                                    <label for="add_bp_type" class="form-label">Tipe Asuransi</label>
                                    <select class="form-select text-uppercase" name="bp_type" id="add_bp_type">
                                        <option value="Asuransi">Asuransi</option>
                                        <option value="BPJS">BPJS</option>
                                        <option value="Dokter">Dokter</option>
                                        <option value="INHEALTH">INHEALTH</option>
                                        <option value="Karyawan - RS">Karyawan - RS</option>
                                        <option value="Perusahaan">Perusahaan</option>
                                        <option value="Pribadi">Pribadi</option>
                                        <option value="Tenant">Tenant</option>
                                        <option value="TGRS">TGRS</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="add_bp_scheme" class="form-label">Skema Asuransi</label>
                                    <select class="form-select text-uppercase" name="bp_scheme" id="add_bp_scheme">
                                        <option value="Corporate">Corporate</option>
                                        <option value="INHEALTH">INHEALTH</option>
                                        <option value="JKN">JKN</option>
                                        <option value="Standard">Standard</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="add_bp_order" class="form-label">Nomor Urutan</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="bp_order" id="add_bp_order" placeholder="Nomor Urutan" required>
                                        <button type="button" class="btn btn-dark bp-order">Gunakan Nomor Terakhir</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="add_bp_contract" class="form-label">Kontrak Asuransi</label>
                                    <input type="text" class="form-control" name="bp_contract" id="add_bp_contract" placeholder="Kontrak Asuransi">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="bp_active" id="add_bp_active_on" value="1" checked>
                                        <label for="add_bp_active_on" class="form-check-label">Aktif</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="bp_active" id="add_bp_active_off" value="0">
                                        <label for="add_bp_active_off" class="form-check-label">Tidak Aktif</label>
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
                            <h4 class="modal-title" id="dark-header-modalLabel">Edit Data Asuransi</h4>
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
                                    <label for="edit_bp_code" class="form-label">Kode Asuransi</label>
                                    <input type="text" class="form-control" name="bp_code" id="edit_bp_code" placeholder="Kode Asuransi" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_bp_name" class="form-label">Nama Asuransi</label>
                                    <input type="text" class="form-control" name="bp_name" id="edit_bp_name" placeholder="Nama Asuransi" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_bp_type" class="form-label">Tipe Asuransi</label>
                                    <select class="form-select text-uppercase" name="bp_type" id="edit_bp_type">
                                        <option value="Asuransi">Asuransi</option>
                                        <option value="BPJS">BPJS</option>
                                        <option value="Dokter">Dokter</option>
                                        <option value="INHEALTH">INHEALTH</option>
                                        <option value="Karyawan - RS">Karyawan - RS</option>
                                        <option value="Perusahaan">Perusahaan</option>
                                        <option value="Pribadi">Pribadi</option>
                                        <option value="Tenant">Tenant</option>
                                        <option value="TGRS">TGRS</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_bp_scheme" class="form-label">Skema Asuransi</label>
                                    <select class="form-select text-uppercase" name="bp_scheme" id="edit_bp_scheme">
                                        <option value="Corporate">Corporate</option>
                                        <option value="INHEALTH">INHEALTH</option>
                                        <option value="JKN">JKN</option>
                                        <option value="Standard">Standard</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_bp_order" class="form-label">Nomor Urutan</label>
                                    <input type="number" class="form-control" name="bp_order" id="edit_bp_order" placeholder="Nomor Urutan" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_bp_contract" class="form-label">Kontrak Asuransi</label>
                                    <input type="text" class="form-control" name="bp_contract" id="edit_bp_contract" placeholder="Kontrak Asuransi">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="bp_active" id="edit_bp_active_on" value="1">
                                        <label for="edit_bp_active_on" class="form-check-label">Aktif</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="bp_active" id="edit_bp_active_off" value="0">
                                        <label for="edit_bp_active_off" class="form-check-label">Tidak Aktif</label>
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
        'resources/js/customs/business-partners.js'
    ])
@endsection
