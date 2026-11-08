@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Daftar Pelajaran', route('instructor.modules.lessons.index', $lesson->module)], ['Beri Poin', null]]">
        <x-slot:title>Beri Poin Manual</x-slot:title>
        <x-slot:subtitle>Kursus: <strong>{{ $course->title }}</strong><br>Modul: <strong>{{ $module->title }}</strong><br>Pelajaran: <strong>{{ $lesson->title }}</strong></x-slot:subtitle>
    </x-dash.page-head>
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daftar Siswa</h5>
                                    <span>
                                        <strong>Kursus:</strong> {{ $course->title }} <br>
                                        <strong>Modul:</strong> {{ $module->title }} <br>
                                        <strong>Pelajaran:</strong> {{ $lesson->title }} <br>
                                        Berikan poin kepada siswa berdasarkan partisipasi atau pencapaian mereka.
                                    </span>
                                </div>
                                <div class="card-block table-border-style">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <x-dash.list-toolbar placeholder="Cari nama, email, atau NIM/NIP/NIDN..." label="Cari siswa" />
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
                                                        <td class="text-center" >{{ $student->studentProfile?->unique_id_number ? $student->studentProfile?->unique_id_number : '-' }}</td>
                                                        <td><a href="{{ route('profile.show', $student->id) }}">{{ $student->name }}</a></td>
                                                        <td>
                                                            {{-- Ambil total poin dari relasi pivot --}}
                                                            <strong>{{ $student->coursePoints->first()->pivot->points_earned ?? 0 }}</strong> Poin
                                                        </td>
                                                        <form action="{{ route('instructor.lesson_points.award', $lesson) }}" method="POST">
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
                                                        <td colspan="4" class="text-center">{{ request()->filled('search') ? 'Tidak ada siswa yang cocok dengan pencarian.' : 'Belum ada siswa yang terdaftar di kursus ini.' }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <x-dash.pagination :items="$students" />
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