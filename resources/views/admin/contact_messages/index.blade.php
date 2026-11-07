@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <x-dash.page-head :crumbs="[['__home', null], ['Pesan Kontak', null]]">
        <x-slot:title>Pesan Kontak Masuk</x-slot:title>
        <x-slot:subtitle>Daftar pesan dari form kontak halaman depan.</x-slot:subtitle>
    </x-dash.page-head>

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
