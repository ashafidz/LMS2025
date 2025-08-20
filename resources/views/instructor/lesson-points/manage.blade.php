@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Beri Poin Manual</h5>
                        <p class="m-b-0">Pelajaran: <strong>{{ $lesson->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}">Daftar Pelajaran</a></li>
                        <li class="breadcrumb-item"><a href="#!">Beri Poin</a></li>
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
                                    <h5>Daftar Siswa di Kursus: {{ $course->title }}</h5>
                                    <span>Berikan poin kepada siswa berdasarkan partisipasi atau pencapaian mereka.</span>
                                </div>
                                <div class="card-block table-border-style">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" >NIM/NIDN/NIP</th>
                                                    <th>Nama Siswa</th>
                                                    <th>Total Poin di Kursus Ini</th>
                                                    <th style="width: 150px;">Beri Poin</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($students as $student)
                                                    <tr>
                                                        <td class="text-center" >{{ $student->unique_id_number ? $student->unique_id_number : '-' }}</td>
                                                        <td><a href="{{ route('profile.show', $student->id) }}">{{ $student->name }}</a></td>
                                                        <td>
                                                            {{-- Ambil total poin dari relasi pivot --}}
                                                            <strong>{{ $student->coursePoints->first()->pivot->points_earned ?? 0 }}</strong> Poin
                                                        </td>
                                                        <form action="{{ route('instructor.lesson_points.award', $lesson->id) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="user_id" value="{{ $student->id }}">
                                                            <td>
                                                                <input type="number" name="points" class="form-control" required min="1" placeholder="e.g., 10">
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="submit" class="btn btn-primary btn-sm">Berikan</button>
                                                            </td>
                                                        </form>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">Belum ada siswa yang terdaftar di kursus ini.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $students->links() }}
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