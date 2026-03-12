@extends('backend.layouts.main', ['page_title' => 'DATA USER'])

@section('css')
    @vite([
        'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
        'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'
    ])
@endsection

@section('content')
    @include('backend.layouts.header', ['title' => 'DATA USER'])
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="basic-datatable" class="table table-striped table-bordered table-centered dt-responsive nowrap w-100">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            @hasanyrole('administrator|sisfo')
                                <th>Created By</th>
                                <th>Updated By</th>
                            @endhasanyrole
                            <th class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td><a href="{{ route('logs.user', $user->username) }}">{{ $user->name }}</a></td>
                                <td>{{ $user->username }}</td>
                                <td class="text-uppercase">{{ $user->roles->first()->name }}</td>
                                @hasanyrole('administrator|sisfo')
                                    <td>{{ $user->created_by }}</td>
                                    <td>{{ $user->updated_by }}</td>
                                @endhasanyrole
                                <td style="max-width: 30px;">
                                    <div class="d-flex align-content-center">
                                        <button type="button" class="btn btn-sm btn-warning ms-2 user-edit" title="EDIT DATA" data-bs-toggle="modal" data-bs-target="#edit-modal" data-user="{{ $user->username }}" {{ auth()->user()->name == $user->name ? 'disabled' : '' }}>
                                            <i class="ri-file-edit-fill"></i>
                                        </button>
                                        <form method="POST" action="{{ route('users.destroy', $user->username) }}">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger ms-2" title="DELETE DATA" onclick="return confirm('Yakin Ingin Menghapus Data?')" {{ auth()->user()->name == $user->name ? 'disabled' : '' }}>
                                                <i class="ri-delete-bin-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
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
                    <div class="modal-body">
                        <div class="modal-header modal-colored-header bg-primary">
                            <h4 class="modal-title" id="dark-header-modalLabel">Tambah Data User</h4>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('users.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="created_by" id="add_created_by" value="{{ auth()->user()->username }}">
                            <div class="mb-3">
                                <label for="add_name" class="form-label">Nama User</label>
                                <input type="text" class="form-control" name="name" id="add_name" placeholder="Nama User" required>
                            </div>
                            <div class="mb-3">
                                <label for="add_username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="add_username" placeholder="Username" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" id="add_password" placeholder="Password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_repeat_password" class="form-label">Ulangi Password</label>
                                    <input type="password" class="form-control" name="repeat_password" id="add_repeat_password" placeholder="Ulangi Password" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="add_role" class="form-label">Role</label>
                                <select class="form-select text-uppercase" name="role" id="add_role">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 text-center">
                                <button class="btn btn-primary" type="submit">SUBMIT</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="edit-modal" class="modal modal-lg fade" tabindex="-1" role="dialog" aria-hidden="true">=
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-colored-header bg-primary">
                        <h4 class="modal-title" id="dark-header-modalLabel">Edit Data User</h4>
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
                                <label for="edit_name" class="form-label">Nama User</label>
                                <input type="text" class="form-control" name="name" id="edit_name" placeholder="Nama User" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="edit_username" placeholder="Username" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" id="edit_password" placeholder="Password">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_repeat_password" class="form-label">Ulangi Password</label>
                                    <input type="password" class="form-control" name="repeat_password" id="edit_repeat_password" placeholder="Ulangi Password">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_role" class="form-label">Role</label>
                                <select class="form-select text-uppercase" name="role" id="edit_role">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
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
@endsection

@section('script')
    @vite([
        'resources/js/customs/datatable.js',
        'resources/js/customs/users.js'
    ])
@endsection
