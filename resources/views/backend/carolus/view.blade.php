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
                    <h4 class="page-title">DATA QR CAROLUS</h4>
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
                                    <th>Bed</th>
                                    <th>Kode</th>
                                    <th>Password</th>
                                    <th>Counter</th>
                                    <th>Aktif</th>
                                    @hasanyrole('administrator|sisfo')
                                        <th>Created By</th>
                                        <th>Updated By</th>
                                        <th class="text-center">Aksi</th>
                                    @endhasanyrole
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($qrcaroluses as $qrcarolus)
                                    <tr>
                                        <td><a href="{{ route('carolus.menu', [$qrcarolus->qrc_room, $qrcarolus->qrc_ucode]) }}">{{ $qrcarolus->qrc_room }}</a></td>
                                        <td><a href="{{ route('qrcarolus.getqrcode', $qrcarolus->qrc_ucode) }}" target="_blank">{{ $qrcarolus->qrc_ucode }}</a></td>
                                        <td>{{ $qrcarolus->qrc_password }}</td>
                                        <td>{{ $qrcarolus->qrc_counter }}</td>
                                        <td>
                                            <span class="fs-20 px-1">
                                                @if($qrcarolus->qrc_active)
                                                    <i class="ri-checkbox-circle-fill text-success"></i>
                                                @else
                                                    <i class="ri-close-circle-fill text-danger"></i>
                                                @endif
                                            </span>
                                        </td>
                                        @hasanyrole('administrator|sisfo')
                                            <td>{{ $qrcarolus->created_by }}</td>
                                            <td>{{ $qrcarolus->updated_by }}
                                            <td style="max-width: 6px;">
                                                <div class="d-flex align-content-center">
                                                    <button type="button" class="btn btn-sm btn-warning qrc-edit" title="EDIT DATA" data-bs-toggle="modal" data-bs-target="#edit-modal" data-qrc-ucode="{{ $qrcarolus->qrc_ucode }}">
                                                        <i class="ri-file-edit-fill"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('qrcarolus.destroy', $qrcarolus->qrc_ucode) }}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger ms-1" title="DELETE DATA" onclick="return confirm('Yakin Ingin Menghapus Data?')">
                                                            <i class="ri-delete-bin-fill"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endhasanyrole
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
                            <h4 class="modal-title" id="dark-header-modalLabel">Tambah Data QR Carolus</h4>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('qrcarolus.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="created_by" id="add_created_by" value="{{ auth()->user()->username }}">
                                <div class="mb-3">
                                    <label for="add_qrc_room" class="form-label">Bed</label>
                                    <input type="text" class="form-control" name="qrc_room" id="add_qrc_room" placeholder="Bed" required>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="qrc_active" id="add_qrc_active_on" value="1" checked>
                                        <label for="add_qrc_active_on" class="form-check-label">Aktif</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="qrc_active" id="add_qrc_active_off" value="0">
                                        <label for="add_qrc_active_off" class="form-check-label">Tidak Aktif</label>
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
                            <h4 class="modal-title" id="dark-header-modalLabel">Edit Data QR Carolus</h4>
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
                                <input type="hidden" name="created_by" id="edit_created_by" value="{{ auth()->user()->username }}">
                                <div class="mb-3">
                                    <label for="edit_qrc_room" class="form-label">Bed</label>
                                    <input type="text" class="form-control" name="qrc_room" id="edit_qrc_room" placeholder="Bed" required>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="qrc_active" id="edit_qrc_active_on" value="1" checked>
                                        <label for="edit_qrc_active_on" class="form-check-label">Aktif</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" class="form-check-input" name="qrc_active" id="edit_qrc_active_off" value="0">
                                        <label for="edit_qrc_active_off" class="form-check-label">Tidak Aktif</label>
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
        'resources/js/customs/qrcarolus.js'
    ])
@endsection
