@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Publikasi Kursus</h5>
                        <p class="m-b-0">Review dan kelola kursus yang diajukan oleh instruktur.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Publikasi</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Kursus Menunggu Review</h5>
                                    <span>Daftar kursus dengan status "Pending Review".</span>
                                </div>
                                <div class="card-block table-border-style">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Judul Kursus</th>
                                                    <th>Instruktur</th>
                                                    <th>Kategori</th>
                                                    <th>Tanggal Diajukan</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($pendingCourses as $course)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration + $pendingCourses->firstItem() - 1 }}</th>
                                                        <td>{{ $course->title }}</td>
                                                        <td>
                                                            <a href="{{ route('profile.show', $course->instructor->id) }}">
                                                                {{ $course->instructor->name }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $course->category->name }}</td>
                                                        <td>{{ $course->updated_at->format('d F Y') }}</td>
                                                        <td class="text-center">
                                                            {{-- Tombol Pratinjau (akan kita fungsikan nanti) --}}
                                                            <a href="{{ route('student.courses.show', ['course' => $course->slug, 'preview' => 'true']) }}" class="btn btn-inverse btn-sm" target="_blank">Pratinjau</a>
                                                            {{-- Tombol Setujui --}}
                                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveModal-{{ $course->id }}">
                                                                Setujui
                                                            </button>
                                                            {{-- Tombol Tolak --}}
                                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal-{{ $course->id }}">
                                                                Tolak
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">Tidak ada kursus yang menunggu review saat ini.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $pendingCourses->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk setiap kursus -->
    @foreach ($pendingCourses as $course)
<!-- Modal Setujui & Tetapkan Harga -->
<div class="modal fade" id="approveModal-{{ $course->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setujui & Publikasikan Kursus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route(Auth::user()->getRoleNames()->first() . '.publication.publish', $course->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Anda akan mempublikasikan kursus: <strong>{{ $course->title }}</strong>.</p>
                    
                    {{-- DIUBAH: Input dinamis berdasarkan tipe pembayaran --}}
                    @if($course->payment_type === 'money')
                        <div class="form-group">
                            <label for="price">Tetapkan Harga Kursus (Rp)</label>
                            <input type="number" name="price" class="form-control" required min="0" placeholder="Contoh: 150000">
                        </div>
                    @elseif($course->payment_type === 'diamonds')
                        <div class="form-group">
                            <label for="diamond_price">Tetapkan Harga Diamond</label>
                            <input type="number" name="diamond_price" class="form-control" required min="0" placeholder="Contoh: 500">
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Publikasikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Modal Tolak -->
        <div class="modal fade" id="rejectModal-{{ $course->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Kursus</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.publication.reject', $course->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body">
                            <p>Anda akan menolak kursus: <strong>{{ $course->title }}</strong>.</p>
                            <div class="form-group">
                                <label for="rejection_reason">Alasan Penolakan (Opsional)</label>
                                <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Jelaskan mengapa kursus ini ditolak agar instruktur bisa memperbaikinya..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Tolak Kursus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection