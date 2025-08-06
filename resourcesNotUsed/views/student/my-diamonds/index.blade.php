@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Diamond Saya</h5>
                        <p class="m-b-0">Lihat saldo dan riwayat transaksi diamond Anda.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Diamondku</a></li>
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
                        <!-- Kartu Saldo Diamond -->
                        <div class="col-md-12 col-lg-4">
                            <div class="card">
                                <div class="card-block text-center">
                                    <i class="fa fa-diamond text-c-blue d-block f-40"></i>
                                    <h4 class="m-t-20"><span class="text-c-blue">{{ number_format($user->diamond_balance, 0, ',', '.') }}</span> Diamond</h4>
                                    <p class="m-b-20">Saldo Diamond Anda Saat Ini</p>
                                    <a href="{{ route('courses') }}" class="btn btn-primary btn-sm btn-round">Gunakan Diamond</a>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Riwayat Diamond -->
                        <div class="col-md-12 col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Riwayat Diamond</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Deskripsi</th>
                                                    <th class="text-right">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($diamondHistories as $history)
                                                    <tr>
                                                        <td>{{ $history->created_at->format('d M Y, H:i') }}</td>
                                                        <td>{{ $history->description }}</td>
                                                        <td class="text-right">
                                                            @if($history->diamonds > 0)
                                                                <span class="text-success font-weight-bold">+{{ $history->diamonds }}</span>
                                                            @else
                                                                <span class="text-danger font-weight-bold">{{ $history->diamonds }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center">Anda belum memiliki riwayat diamond.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $diamondHistories->links() }}
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