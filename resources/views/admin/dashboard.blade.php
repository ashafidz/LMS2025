@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <x-dash.page-head :crumbs="[['__home', route('home')], ['Dashboard', null]]">
        <x-slot:title>Dashboard</x-slot:title>
        <x-slot:subtitle>Selamat datang di Dashboard Admin {{ Auth::user()->name }}</x-slot:subtitle>
    </x-dash.page-head>
    <!-- Page-header end -->
    <div class="container-fluid px-0 pb-5">

        <!-- Baris Kartu Statistik -->
        <div class="row">

            <!-- Total Instruktur -->
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="rs-stat h-100">
                    <span class="rs-tile" aria-hidden="true"><i class="fa fa-users"></i></span>
                    <div>
                        <strong>{{ number_format($totalInstructors) }}</strong>
                        <span>Total Instruktur</span>
                        <span class="rs-stat__note">
                            @if($instructorGrowth > 0)
                                +{{ $instructorGrowth }}% bulan ini
                            @elseif($instructorGrowth < 0)
                                {{ $instructorGrowth }}% bulan ini
                            @else
                                Tidak ada perubahan
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Kursus -->
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="rs-stat h-100">
                    <span class="rs-tile" aria-hidden="true"><i class="fa fa-book"></i></span>
                    <div>
                        <strong>{{ number_format($totalCourses) }}</strong>
                        <span>Total Kursus</span>
                        <span class="rs-stat__note">+{{ $newCoursesThisMonth }} kursus baru bulan ini</span>
                    </div>
                </div>
            </div>

            <!-- Total Unique Students -->
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="rs-stat h-100">
                    <span class="rs-tile" aria-hidden="true"><i class="fa fa-user-check"></i></span>
                    <div>
                        <strong>{{ number_format($totalStudentsAllCourses) }}</strong>
                        <span>Total Siswa Unik</span>
                        <span class="rs-stat__note">
                            @if($enrollmentGrowth > 0)
                                +{{ $enrollmentGrowth }}% dibanding bulan lalu
                            @elseif($enrollmentGrowth < 0)
                                {{ $enrollmentGrowth }}% dibanding bulan lalu
                            @else
                                Tidak ada perubahan
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Enrollments -->
            <div class="col-xl-3 col-md-6 col-12 mb-3">
                <div class="rs-stat h-100">
                    <span class="rs-tile" aria-hidden="true"><i class="fa fa-graduation-cap"></i></span>
                    <div>
                        <strong>{{ number_format($totalEnrollments) }}</strong>
                        <span>Total Enrollment</span>
                        <span class="rs-stat__note">Semua pendaftaran kursus</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Baris Kedua: Tabel Data -->
        <div class="row mt-2">

            <!-- Instruktur dengan Kursus Terbanyak -->
            <div class="col-xl-6 col-md-12 mb-3">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Instruktur dengan Kursus Terbanyak</h5>
                        <input type="text" class="form-control form-control-sm w-50" placeholder="Cari instruktur..." id="searchInstructor">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 rs-simple-table">
                                <thead>
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

            <!-- Kategori dengan Siswa Terbanyak -->
            <div class="col-xl-6 col-md-12 mb-3">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kategori dengan Siswa Terbanyak</h5>
                        <input type="text" class="form-control form-control-sm w-50" placeholder="Cari kategori..." id="searchCategory">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 rs-simple-table">
                                <thead>
                                    <tr>
                                        <th>Nama Kategori</th>
                                        <th>Total Siswa</th>
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
                url: '{{ route("dashboard.search.instructors") }}',
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
                url: '{{ route("dashboard.search.categories") }}',
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