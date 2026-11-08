
@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('home')], ['Pendaftaran Instruktur', null]]">
            <x-slot:title>Pendaftaran Instruktur</x-slot:title>
            <x-slot:subtitle>Tinjau pendaftaran instruktur, lalu setujui atau tolak.</x-slot:subtitle>
        </x-dash.page-head>
        <!-- Page-header end -->
        <div class="pcoded-inner-content">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page-body start -->
                    <div class="page-body">
                        <div class="row">

                            <!--  project and team member start -->
                            <div class="col-sm-12">
                                <div class="card table-card">
                                    <div class="card-header">
                                        <h5>Pendaftaran Instruktur</h5>
                                        <p>List pendaftaran sebagai Instructor</p>
                                        <div class="card-header-right">
                                            {{-- <ul class="list-unstyled card-option">
                                                <li>
                                                    <i class="fa fa fa-wrench open-card-option"></i>
                                                </li>
                                                <li>
                                                    <i class="fa fa-window-maximize full-card"></i>
                                                </li>
                                                <li>
                                                    <i class="fa fa-minus minimize-card"></i>
                                                </li>
                                                <li>
                                                    <i class="fa fa-refresh reload-card"></i>
                                                </li>
                                                <li>
                                                    <i class="fa fa-trash close-card"></i>
                                                </li>
                                            </ul> --}}
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <x-dash.list-toolbar placeholder="Cari nama, email, atau headline pelamar..." label="Cari pengajuan" />
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            Instructor Name
                                                        </th>
                                                        <th>
                                                            Email
                                                        </th>
                                                        <th>
                                                            Portofolio
                                                        </th>
                                                        <th class="text-center">
                                                            Application Status
                                                        </th>
                                                        <th class="text-center">
                                                            Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($applications as $application)

                                                    {{-- handle route --}}
                                                    @php
                                                        $actionRouteApprove = Auth::user()->hasRole('admin') 
                                                        ? route('admin.instructor-applications.approve', $application)
                                                        : route('superadmin.instructor-applications.approve', $application);

                                                        $actionRouteReject = Auth::user()->hasRole('admin') 
                                                        ? route('admin.instructor-applications.reject', $application)
                                                        : route('superadmin.instructor-applications.reject', $application);

                                                        $actionRouteDeactive = Auth::user()->hasRole('admin') 
                                                        ? route('admin.instructor-applications.deactive', $application)
                                                        : route('superadmin.instructor-applications.deactive', $application);

                                                                   
                                                        $actionRouteReactivate = Auth::user()->hasRole('admin') 
                                                            ? route('admin.instructor-applications.reactivate', $application) 
                                                            : route('superadmin.instructor-applications.reactivate', $application); 
                                                    @endphp
                                                    {{-- end handle route --}}


                                                        <tr>
                                                            <td>
                                                                <div class="d-inline-block align-middle">
                                                                    <a href="{{ route('profile.show', $application->user->id) }}">
                                                                        {{-- retrive from users profile_picture_url --}}
                                                                        <img src="{{ $application->user->profile_picture_url ? asset($application->user->profile_picture_url) : 'https://placehold.co/32x32/EBF4FF/767676?text=SA' }}"
                                                                            alt="user image"
                                                                            class="img-radius img-40 align-top m-r-15" />
                                                                    </a>
                                                                    <div class="d-inline-block">
                                                                        <h6>
                                                                            <a href="{{ route('profile.show', $application->user->id) }}">{{ $application->user->name }}</a>
                                                                        </h6>
                                                                        <p class="text-muted m-b-0">
                                                                            {{ $application->headline }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                {{ $application->user->email }}
                                                            </td>
                                                            <td>
                                                                <a href="{{ $application->website_url }}" target="_blank"
                                                                    rel="noopener noreferrer">
                                                                    View Link
                                                                </a>
                                                            </td>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge 
                                                                    @if ($application->application_status == 'pending') bg-warning text-dark 
                                                                    @elseif($application->application_status == 'approved') bg-success 
                                                                    @else bg-danger @endif">
                                                                    {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'deactive' => 'Nonaktif'][$application->application_status] ?? ucfirst($application->application_status) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($application->application_status === 'pending')
                                                                    <div class="btn-group d-flex justify-content-center" role="group">
                                                                        <form
                                                                            action="{{ $actionRouteApprove }}"
                                                                            method="POST"
                                                                            data-confirm="Apakah kamu yakin ingin menyetujui pendaftaran instructor ini?" data-confirm-ok="Ya, setujui">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-success">Approve</button>
                                                                        </form>
                                                                        <form
                                                                            action="{{ $actionRouteReject }}"
                                                                            method="POST"
                                                                            data-confirm="Apakah kamu yakin ingin menolak pendaftaran instructor ini?" data-confirm-danger data-confirm-ok="Ya, tolak"
                                                                            class="ms-1">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-outline-danger">Reject</button>
                                                                        </form>
                                                                    </div>
                                                                @elseif ($application->application_status === 'approved') 
                                                                    <div class="btn-group d-flex justify-content-center" role="group">
                                                                        <form
                                                                            action="{{ $actionRouteDeactive }}"
                                                                            method="POST"
                                                                            data-confirm="Apakah kamu yakin ingin menonaktifkan instructor ini?" data-confirm-danger data-confirm-ok="Ya, nonaktifkan">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-outline-danger">Deactive</button>
                                                                        </form>
                                                                    </div>
                                                                @elseif ($application->application_status === 'deactive')
                                                                    <div class="btn-group d-flex justify-content-center" role="group">
                                                                        <form
                                                                            action="{{ $actionRouteReactivate }}"
                                                                            method="POST"
                                                                            data-confirm="Apakah kamu yakin ingin mengaktifkan kembali instructor ini?" data-confirm-ok="Ya, aktifkan">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-secondary">Ractive</button>
                                                                        </form>
                                                                    </div>

                                                                @else
                                                                    <span ><p class="text-center">No actions</p></span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">{{ request()->filled('search') ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada pengajuan instruktur.' }}
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <x-dash.pagination :items="$applications" />
                                    </div>
                                </div>
                            </div>
                            <!--  project and team member end -->
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
                <div id="styleSelector"></div>
            </div>
        </div>
    </div>
@endsection
