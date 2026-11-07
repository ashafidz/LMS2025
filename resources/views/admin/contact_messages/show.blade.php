@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <x-dash.page-head :crumbs="[['__home', null], ['Pesan Kontak', Auth::user()->role === 'superadmin' ? route('superadmin.contact-messages.index') : route('admin.contact-messages.index')], ['Detail', null]]">
        <x-slot:title>Detail Pesan Kontak</x-slot:title>
        <x-slot:subtitle>Membaca pesan dari {{ $contactMessage->name }}</x-slot:subtitle>
    </x-dash.page-head>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Isi Pesan</h5>
                                    @if($contactMessage->is_replied)
                                        <span class="badge badge-success float-right">Sudah Dibalas ({{ \Carbon\Carbon::parse($contactMessage->replied_at)->format('d M Y, H:i') }})</span>
                                    @endif
                                </div>
                                <div class="card-block">
                                    
                                    <div class="mb-3">
                                        <label class="font-weight-bold">Subjek:</label>
                                        <h4 class="mt-1">{{ $contactMessage->subject }}</h4>
                                    </div>
                                    <div class="mb-4">
                                        <label class="font-weight-bold">Pesan:</label>
                                        <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">{{ $contactMessage->message }}</div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="font-weight-bold mt-4">Balas Pesan</h6>
                                    @if(!$contactMessage->is_replied)
                                    <p class="text-muted">Gunakan form di bawah ini untuk mengirimkan email balasan langsung ke {{ $contactMessage->email }}.</p>
                                    <form action="{{ Auth::user()->role === 'superadmin' ? route('superadmin.contact-messages.reply', $contactMessage->id) : route('admin.contact-messages.reply', $contactMessage->id) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <textarea name="reply_message" class="form-control" rows="6" placeholder="Ketik balasan Anda di sini..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Kirim Balasan via Email</button>
                                    </form>
                                    @else
                                    <div class="alert alert-info">Pesan ini sudah dibalas. Anda tidak dapat mengirim balasan ganda.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Informasi Pengirim</h5>
                                </div>
                                <div class="card-block">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th scope="row" style="width: 40%">Nama</th>
                                                <td>: {{ $contactMessage->name }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Email</th>
                                                <td>: <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Dikirim Pada</th>
                                                <td>: {{ $contactMessage->created_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
