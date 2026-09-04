@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Pesan Kontak Masuk</h5>
                        <p class="m-b-0">Daftar pesan dari form kontak halaman depan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="#"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Pesan Kontak</a></li>
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
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daftar Pesan Kontak</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Tgl Masuk</th>
                                                    <th>Nama Pengirim</th>
                                                    <th>Email</th>
                                                    <th>Subjek</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($messages as $msg)
                                                <tr>
                                                    <td>{{ $msg->created_at->format('d M Y, H:i') }}</td>
                                                    <td>{{ $msg->name }}</td>
                                                    <td>{{ $msg->email }}</td>
                                                    <td>{{ Str::limit($msg->subject, 30) }}</td>
                                                    <td>
                                                        @if($msg->is_replied)
                                                            <label class="badge badge-success">Dibalas</label>
                                                        @elseif($msg->is_read)
                                                            <label class="badge badge-info">Dibaca</label>
                                                        @else
                                                            <label class="badge badge-warning">Baru</label>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(Auth::user()->role === 'superadmin')
                                                            <a href="{{ route('superadmin.contact-messages.show', $msg->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> Buka</a>
                                                        @else
                                                            <a href="{{ route('admin.contact-messages.show', $msg->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> Buka</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">Belum ada pesan kontak yang masuk.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $messages->links() }}
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
@endsection
