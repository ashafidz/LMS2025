
@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">
                                Dashboard
                            </h5>
                            <p class="m-b-0">
                                Selamat datang di Dashboard Admin
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#!">Instructor Application</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
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
                                        <h5>Instructor Application</h5>
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
                                                                    {{-- retrive from users profile_picture_url --}}
                                                                    <img src="{{ $application->user->profile_picture_url ?? 'https://placehold.co/32x32/EBF4FF/767676?text=SA' }}"
                                                                        alt="user image"
                                                                        class="img-radius img-40 align-top m-r-15" />
                                                                    <div class="d-inline-block">
                                                                        <h6>
                                                                            {{ $application->user->name }}
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
                                                                    {{ ucfirst($application->application_status) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($application->application_status === 'pending')
                                                                    <div class="btn-group d-flex justify-content-center" role="group">
                                                                        <form
                                                                            action="{{ $actionRouteApprove }}"
                                                                            method="POST"
                                                                            onsubmit="return confirm('Apakah kamu yakin ingin Approve pendaftaran instructor ini');">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-success">Approve</button>
                                                                        </form>
                                                                        <form
                                                                            action="{{ $actionRouteReject }}"
                                                                            method="POST"
                                                                            onsubmit="return confirm('Apakah kamu yakin ingin Reject pendaftaran instructor ini?');"
                                                                            class="ms-1">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-danger">Reject</button>
                                                                        </form>
                                                                    </div>
                                                                @elseif ($application->application_status === 'approved') 
                                                                    <div class="btn-group d-flex justify-content-center" role="group">
                                                                        <form
                                                                            action="{{ $actionRouteDeactive }}"
                                                                            method="POST"
                                                                            onsubmit="return confirm('Apakah kamu yakin ingin deactive Instructor ini?');">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-danger">Deactive</button>
                                                                        </form>
                                                                    </div>
                                                                @elseif ($application->application_status === 'deactive')
                                                                    <div class="btn-group d-flex justify-content-center" role="group">
                                                                        <form
                                                                            action="{{ $actionRouteReactivate }}"
                                                                            method="POST"
                                                                            onsubmit="return confirm('Apakah kamu yakin ingin Ractive Instructor ini?');">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-success">Ractive</button>
                                                                        </form>
                                                                    </div>

                                                                @else
                                                                    <span ><p class="text-center">No actions</p></span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">No applications found.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="card-footer">
                                            {{ $applications->links() }}
                                        </div>
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
