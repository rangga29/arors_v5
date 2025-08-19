@extends('backend.layouts.main', ['page_title' => 'CEK BPJS - PESERTA - HASIL'])

@section('css')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css',])
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right"></div>
                    <h4 class="page-title">CEK BPJS - SEP - HASIL</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if(!empty($data) && is_array($data))
                            @foreach ($status as $key => $value)
                                <strong>{{ $key }}:</strong>
                                @if(is_array($value))
                                    <pre>{{ print_r($value, true) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                                <br>
                            @endforeach
                            <hr>
                            @foreach ($data as $key => $value)
                                <strong>{{ $key }}:</strong>
                                @if(is_array($value))
                                    <pre>{{ print_r($value, true) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                                <br>
                            @endforeach
                        @else
                            @foreach ($status as $key => $value)
                                <strong>{{ $key }}:</strong>
                                @if(is_array($value))
                                    <pre>{{ print_r($value, true) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                                <br>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
