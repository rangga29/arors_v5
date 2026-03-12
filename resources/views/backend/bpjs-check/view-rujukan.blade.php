@extends('backend.layouts.main', ['page_title' => 'CEK BPJS - RUJUKAN'])

@section('css')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">CEK BPJS - RUJUKAN</h4>
                </div>
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
                        <form class="ps-3 pe-3 mt-2 mb-4" action="{{ route('cek-bpjs.cek-rujukan') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="rujukan" class="form-label control-label">No Rujukan</label>
                                <input type="text" class="form-control" name="rujukan" id="rujukan" placeholder="No Rujukan" required>
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
