@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content">
    <x-dash.page-head :crumbs="[['__home', route('superadmin.dashboard')], ['Kelola Admin', null]]">
        <x-slot:title>Kelola Admin</x-slot:title>
    </x-dash.page-head>
    <div class="pcoded-inner-content"><div class="main-body"><div class="page-wrapper"><div class="page-body">
        <div class="row"><div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Daftar Admin</h5>
                    <span>Jumlah admin: {{ $admins->count() }}</span>
                    <div class="card-header-right">
                        <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary">Tambah Admin Baru</a>
                    </div>
                </div>
                <div class="card-block table-border-style">
                    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Nama</th><th>Email</th><th class="text-center">Aksi</th></tr></thead>
                            <tbody>
                                @forelse ($admins as $admin)
                                    <tr>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('superadmin.admins.edit', $admin->id) }}" class="btn btn-secondary btn-sm"><i class="fa fa-pencil"></i> Edit</a>
                                            <form action="{{ route('superadmin.admins.destroy', $admin->id) }}" method="POST" class="d-inline" data-confirm="Apakah Anda yakin ingin menghapus admin ini?" data-confirm-danger data-confirm-ok="Ya, hapus">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i> Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center">Belum ada admin yang ditambahkan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">{{ $admins->links() }}</div>
                </div>
            </div>
        </div></div>
    </div></div></div></div>
</div>
@endsection