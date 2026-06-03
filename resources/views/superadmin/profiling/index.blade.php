@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Manajemen Template Profiling</h5>
                        <p class="m-b-0">Kelola komponen global untuk Adaptive Course</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row">
                        @foreach($components as $component)
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ $component->name }}</h5>
                                </div>
                                <div class="card-block">
                                    <p>{{ $component->description }}</p>
                                    <ul>
                                        <li><strong>Tipe:</strong> {{ ucfirst($component->mechanics_type) }}</li>
                                        <li><strong>Skala:</strong> {{ $component->scale_min }} - {{ $component->scale_max }}</li>
                                        <li><strong>Teori:</strong> {{ $component->theory_reference }}</li>
                                    </ul>
                                    <a href="{{ route('superadmin.profiling-components.show', $component->id) }}" class="btn btn-primary btn-sm btn-block mt-3">
                                        Kelola Dimensi & Soal
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
