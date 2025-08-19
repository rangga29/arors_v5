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
                    <h4 class="page-title">PRINT OLD SEP</h4>
                </div>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="alert" aria-label="Close"></button>
                        <strong>SUCCESS - </strong>{{ session('success') }}
                    </div>
                @endif
                @if(session('status_detail'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="alert" aria-label="Close"></button>
                        <strong>DETAIL STATUS:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach(session('status_detail') as $item)
                                <li>
                                    <strong>{{ $item['kode'] }}</strong> - {{ $item['status'] }} (HTTP {{ $item['http_code'] }})
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('status_summary'))
                    <div class="alert alert-info">
                        <p>Total kode dimasukkan: {{ session('status_summary.total_input') }}</p>
                        <p>Total kode diproses: {{ session('status_summary.total_processed') }}</p>
                        <p>Status:
                            @if(session('status_summary.match'))
                                <span class="text-success">Cocok</span>
                            @else
                                <span class="text-danger">Tidak Cocok</span>
                            @endif
                        </p>
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
                        <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('data-migration.get-print-old-sep') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="kode_list" class="form-label">Masukkan Kode (pisahkan per baris)</label>
                                <textarea name="kode_list" id="kode_list" rows="6" class="form-control"></textarea>
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
