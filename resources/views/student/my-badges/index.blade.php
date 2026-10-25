@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Badge Pencapaian</h5>
                        <p class="m-b-0">Lihat semua pencapaian yang bisa Anda raih dan yang sudah Anda dapatkan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Badge Saya</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        @forelse ($allBadges as $badge)
                            <div class="col-md-6 col-lg-4">
                                <div class="card text-center {{ $badge->has_badge ? '' : 'bg-light' }}">
                                    <div class="card-block">
                                        <img src="{{ asset('storage/' . $badge->icon_path) }}" alt="{{ $badge->title }}" class="img-fluid rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover; {{ $badge->has_badge ? '' : 'filter: grayscale(100%);' }}">
                                        <h5 class="card-title font-weight-bold">{{ $badge->title }}</h5>
                                        <p class="card-text text-muted">{{ $badge->description }}</p>
                                        
                                        @if($badge->has_badge)
                                            <div class="text-success font-weight-bold">
                                                <i class="fa fa-check-circle"></i> Diperoleh
                                            </div>
                                            @if($badge->earned_at)
                                                <small class="text-muted">Didapat pada {{ $badge->earned_at->format('d M Y') }}</small>
                                            @endif
                                        @elseif($badge->progress)
                                            <div class="text-left">
                                                @foreach($badge->progress['rules'] as $rule)
                                                    @php
                                                        $percent = $rule['target'] > 0 ? min(100, round($rule['current'] / $rule['target'] * 100)) : 0;
                                                    @endphp
                                                    <div class="small mb-1">{{ $rule['sentence'] }}</div>
                                                    <div class="progress" style="height: 12px;">
                                                        <div class="progress-bar {{ $rule['met'] ? 'bg-success' : '' }}" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <div class="small text-muted mb-2">{{ min($rule['current'], $rule['target']) }} dari {{ $rule['target'] }} {{ $rule['unit'] }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted"><i class="fa fa-user"></i> Diberikan oleh admin</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                             <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-block text-center p-5">
                                        <p class="text-muted">Belum ada badge yang tersedia saat ini.</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection