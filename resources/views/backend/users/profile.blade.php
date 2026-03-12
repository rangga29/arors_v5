@extends('backend.layouts.main', ['page_title' => 'MY PROFILE'])

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
                    <h4 class="page-title">MY ACCOUNT</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-lg-4">
                <div class="card text-center">
                    <div class="card-body">
                        <img src="{{ asset('images/rsck_logo.png') }}" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">
                        <h4 class="mb-1 mt-2">{{ $user->name }}</h4>
                        <p class="text-muted text-uppercase">{{ $user->roles->first()->name }}</p>
                        <hr>
                        <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('users.profile.update', $user->username) }}" method="POST">
                            @method('PUT')
                            @csrf
                            <input type="hidden" name="updated_by" id="edit_updated_by" value="{{ auth()->user()->name }}">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Nama User</label>
                                <input type="text" class="form-control" name="name" id="edit_name" placeholder="Nama User" value="{{ $user->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="edit_password" placeholder="Password">
                            </div>
                            <div class="mb-3">
                                <label for="edit_repeat_password" class="form-label">Ulangi Password</label>
                                <input type="password" class="form-control" name="repeat_password" id="edit_repeat_password" placeholder="Ulangi Password">
                            </div>
                            <div class="mb-3 text-center">
                                <button class="btn btn-primary" type="submit">UPDATE DATA</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-9 col-lg-8">
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
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3">DATA LOG</h4>
                        <table id="basic-datatable" class="table table-striped table-bordered table-centered dt-responsive nowrap w-100">
                            <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Module</th>
                                <th>Pesan Log</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->lo_time)->isoFormat('DD MMMM YYYY - hh:mm:ss') }}</td>
                                    <td>{{ $log->lo_module }}</td>
                                    <td>{{ $log->lo_message }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/customs/datatable.js'])
@endsection
