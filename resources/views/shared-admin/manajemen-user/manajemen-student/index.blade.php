
@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">
                                Dashboard
                            </h5>
                            <p class="m-b-0">
                                Selamat datang di Dashboard Admin
                            </p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#!">Manajemen Student</a>
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
                                        <h5>List Student</h5>
                                        <p>List student yang terdaftar</p>
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
                                                        <th class="text-center">
                                                            NIM/NIP/NIDN
                                                        </th>
                                                        <th>
                                                            Nama Student
                                                        </th>
                                                        <th>
                                                            Email
                                                        </th>
                                                        <th class="text-center">
                                                            Student Status
                                                        </th>
                                                        <th class="text-center">
                                                            Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($students_status_data as $student_status_data)

                                                    {{-- handle route --}}
                                                    @php
                                                        $actionRouteDeactive = Auth::user()->hasRole('admin') 
                                                        ? route('admin.manajemen-student.deactive', $student_status_data)
                                                        : route('superadmin.manajemen-student.deactive', $student_status_data);

                                                                   
                                                        $actionRouteReactivate = Auth::user()->hasRole('admin') 
                                                            ? route('admin.manajemen-student.reactivate', $student_status_data) 
                                                            : route('superadmin.manajemen-student.reactivate', $student_status_data); 
                                                    @endphp
                                                    {{-- end handle route --}}


                                                        <tr>
                                                            <td>
                                                                <p class="text-center">{{ $student_status_data->unique_id_number ? $student_status_data->unique_id_number : '-' }}</p>
                                                            </td>
                                                            <td>
                                                                <div class="d-inline-block align-middle">
                                                                    <a href="{{ route('users.show', $student_status_data->user->id) }}">
                                                                        {{-- retrive from users profile_picture_url --}}
                                                                        <img src="{{ $student_status_data->user->profile_picture_url ? asset($student_status_data->user->profile_picture_url) : 'https://placehold.co/32x32/EBF4FF/767676?text=SA' }}"
                                                                            alt="user image"
                                                                            class="img-radius img-40 align-top m-r-15" />
                                                                    </a>
                                                                    <div class="d-inline-block">
                                                                        <h6>
                                                                            <a href="{{ route('users.show', $student_status_data->user->id) }}">{{ $student_status_data->user->name }}</a>
                                                                        </h6>
                                                                        <p class="text-muted m-b-0">
                                                                            {{ $student_status_data->headline }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                {{ $student_status_data->user->email }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge 
                                                                    @if ($student_status_data->student_status == 'pending') bg-warning text-dark 
                                                                    @elseif($student_status_data->student_status == 'active') bg-success 
                                                                    @else bg-danger @endif">
                                                                    {{ ucfirst($student_status_data->student_status) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($student_status_data->student_status === 'active') 
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
                                                                @elseif ($student_status_data->student_status === 'deactive')
                                                                    <div class="btn-group d-flex justify-content-center" role="group">
                                                                        <form
                                                                            action="{{ $actionRouteReactivate }}"
                                                                            method="POST"
                                                                            onsubmit="return confirm('Apakah kamu yakin ingin Re-active Instructor ini?');">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-success">Re-active</button>
                                                                        </form>
                                                                    </div>

                                                                @else
                                                                    <span ><p class="text-center">No actions</p></span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">Tidak ada data.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="card-footer">
                                            {{ $students_status_data->links() }}
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
