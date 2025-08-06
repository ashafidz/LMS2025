@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Poin Saya</h5>
                        <p class="m-b-0">Lihat total poin dan riwayat perolehan poin Anda di setiap kursus.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Poinku</a></li>
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
                        <div class="col-sm-12">
                            <div class="card widget-visitor-card">
                                <div class="card-block-big text-center">
                                    <i class="ti-medall-alt text-warning f-40"></i>
                                    <h4 class="m-t-20"><span class="text-warning">{{ number_format($totalPoints, 0, ',', '.') }}</span> Poin</h4>
                                    <p>Total Akumulasi Poin anda saat ini</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Rincian Poin per Kursus</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nama Kursus</th>
                                                    <th>Poin Diperoleh</th>
                                                    <th>Status Konversi</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- CORRECTED: Renamed $pivot to $course for clarity --}}
                                                @forelse ($pointsPerCourse as $course)
                                                    <tr>
                                                        {{-- CORRECTED: Access title directly --}}
                                                        <td>{{ $course->title }}</td>
                                                        
                                                        {{-- CORRECTED: Access pivot data via the ->pivot attribute --}}
                                                        <td><strong>{{ $course->pivot->points_earned }} Poin</strong></td>
                                                        <td>
                                                            {{-- CORRECTED: Access pivot data via the ->pivot attribute --}}
                                                            @if($course->pivot->is_converted_to_diamond)
                                                                <label class="label label-success">Sudah Dikonversi</label>
                                                            @else
                                                                <label class="label label-default">Belum Dikonversi</label>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            {{-- CORRECTED: Use the course's own ID --}}
                                                            <button class="btn btn-inverse btn-sm" data-toggle="modal" data-target="#historyModal-{{ $course->id }}">
                                                                Lihat Riwayat
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">Anda belum mendapatkan poin dari kursus manapun.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $pointsPerCourse->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CORRECTED: Renamed $pivot to $course for clarity --}}
@foreach($pointsPerCourse as $course)
{{-- CORRECTED: Use the course's own ID for the modal ID --}}
<div class="modal fade" id="historyModal-{{ $course->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                {{-- CORRECTED: Access title directly --}}
                <h5 class="modal-title">Riwayat Poin: {{ $course->title }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    {{-- CORRECTED: Use the course's own ID to look up its history --}}
                    @forelse($pointHistories[$course->id] ?? [] as $history)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                {{ $history->description }}
                                <br>
                                <small class="text-muted">{{ $history->created_at->format('d M Y, H:i') }}</small>
                            </div>
                            <span class="badge badge-primary badge-pill">+{{ $history->points }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Tidak ada riwayat untuk kursus ini.</li>
                    @endforelse
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection