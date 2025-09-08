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
                            Selamat datang di Dashboard Admin {{ Auth::user()->name }}
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
                            <a href="#!">Dashboard</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->
    <div class="container-fluid mt-5 px-4 pb-5">

        <!-- Baris Kartu Statistik -->
        <div class="row gy-4 gx-4">

            <!-- Total Instruktur -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="text-primary mb-1">{{ number_format($totalInstructors) }}</h3>
                                <p class="text-muted mb-0">Total Instruktur</p>
                            </div>
                            <i class="fa fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-primary text-white d-flex justify-content-between align-items-center py-2 px-4">
                        <small>
                            @if($instructorGrowth > 0)
                                +{{ $instructorGrowth }}% bulan ini
                            @elseif($instructorGrowth < 0)
                                {{ $instructorGrowth }}% bulan ini
                            @else
                                Tidak ada perubahan
                            @endif
                        </small>
                        <i class="fa fa-line-chart"></i>
                    </div>
                </div>
            </div>

            <!-- Total Kursus -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="text-danger mb-1">{{ number_format($totalCourses) }}</h3>
                                <p class="text-muted mb-0">Total Kursus</p>
                            </div>
                            <i class="fa fa-book fa-2x text-danger"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-danger text-white d-flex justify-content-between align-items-center py-2 px-4">
                        <small>+{{ $newCoursesThisMonth }} kursus baru bulan ini</small>
                        <i class="fa fa-line-chart"></i>
                    </div>
                </div>
            </div>

            <!-- Total Unique Students -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="text-success mb-1">{{ number_format($totalStudentsAllCourses) }}</h3>
                                <p class="text-muted mb-0">Total Student Unik</p>
                            </div>
                            <i class="fa fa-user-check fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-success text-white d-flex justify-content-between align-items-center py-2 px-4">
                        <small>
                            @if($enrollmentGrowth > 0)
                                +{{ $enrollmentGrowth }}% dibanding bulan lalu
                            @elseif($enrollmentGrowth < 0)
                                {{ $enrollmentGrowth }}% dibanding bulan lalu
                            @else
                                Tidak ada perubahan
                            @endif
                        </small>
                        <i class="fa fa-line-chart"></i>
                    </div>
                </div>
            </div>

            <!-- Total Enrollments -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1" style="color: orange;">{{ number_format($totalEnrollments) }}</h3>
                                <p class="text-muted mb-0">Total Enrollment</p>
                            </div>
                            <i class="fa fa-graduation-cap fa-2x" style="color: orange;"></i>
                        </div>
                    </div>
                    <div class="card-footer text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: orange;">
                        <small>Semua pendaftaran kursus</small>
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Baris Kedua: Tabel Data -->
        <div class="row g-4 mt-4">

            <!-- Instruktur dengan Kursus Terbanyak -->
            <div class="col-xl-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Instruktur dengan Kursus Terbanyak</h5>
                        <input type="text" class="form-control form-control-sm w-50" placeholder="Cari instruktur..." id="searchInstructor">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Instruktur</th>
                                        <th>Total Kursus</th>
                                    </tr>
                                </thead>
                                <tbody id="instructorTableBody">
                                    @forelse($instructorsWithCourses as $instructor)
                                    <tr>
                                        <td>{{ $instructor->name }}</td>
                                        <td><span class="badge bg-primary">{{ $instructor->courses_count }}</span></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Belum ada data instruktur</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- <div class="text-center mt-3">
                                <a href="{{ route('admin.users.index') }}" class="b-b-primary text-primary">Lihat Semua Instruktur</a>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategori dengan Student Terbanyak -->
            <div class="col-xl-6 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kategori dengan Student Terbanyak</h5>
                        <input type="text" class="form-control form-control-sm w-50" placeholder="Cari kategori..." id="searchCategory">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Kategori</th>
                                        <th>Total Students</th>
                                    </tr>
                                </thead>
                                <tbody id="categoryTableBody">
                                    @forelse($categoriesWithStudents as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td><span class="badge bg-success">{{ $category->total_students }}</span></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Belum ada data kategori</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- <div class="text-center mt-3">
                                <a href="{{ route('admin.course-categories.index') }}" class="b-b-primary text-primary">Lihat Semua Kategori</a>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search Instruktur
    let searchInstructorTimeout;
    $('#searchInstructor').on('input', function() {
        const search = $(this).val();
        
        clearTimeout(searchInstructorTimeout);
        searchInstructorTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("superadmin.dashboard.search.instructors") }}',
                type: 'GET',
                data: { search: search },
                success: function(response) {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach(function(instructor) {
                            html += `
                                <tr>
                                    <td>${instructor.name}</td>
                                    <td><span class="badge bg-primary">${instructor.courses_count}</span></td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="2" class="text-center text-muted">Tidak ada data ditemukan</td></tr>';
                    }
                    $('#instructorTableBody').html(html);
                },
                error: function() {
                    console.log('Error searching instructors');
                }
            });
        }, 300);
    });
    
    // Search Kategori
    let searchCategoryTimeout;
    $('#searchCategory').on('input', function() {
        const search = $(this).val();
        
        clearTimeout(searchCategoryTimeout);
        searchCategoryTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("superadmin.dashboard.search.categories") }}',
                type: 'GET',
                data: { search: search },
                success: function(response) {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach(function(category) {
                            html += `
                                <tr>
                                    <td>${category.name}</td>
                                    <td><span class="badge bg-success">${category.total_students}</span></td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="2" class="text-center text-muted">Tidak ada data ditemukan</td></tr>';
                    }
                    $('#categoryTableBody').html(html);
                },
                error: function() {
                    console.log('Error searching categories');
                }
            });
        }, 300);
    });
});
</script>
@endpush