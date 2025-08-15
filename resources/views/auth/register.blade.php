@extends('layouts.app-layout')

@section('content')
<section class="login-block">
    <div class="container-fluid px-0">
        <div class="row no-gutters shift-up" style="min-height: 100vh;">

            <!-- Form Registrasi -->
            <div class="col-12 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
                <form class="md-float-material form-material w-100 px-4" method="POST" action="{{ route('register') }}" style="max-width: 600px; width: 100%;">

                    @csrf
                    <div class="auth-box card">
                        <div class="card-block">

                            <div class="text-center mb-3">
                                <h3 class="text-center">Daftar</h3>
                                <p class="font-weight-bold">Daftar Sebagai</p>
                                <div class="d-flex justify-content-center mb-2">
                                    <div class="role-btn btn btn-primary mr-2 active" onclick="selectRole(this, 'student')">
                                        👨‍🎓 Siswa
                                    </div>
                                    <div class="role-btn btn btn-outline-secondary" onclick="selectRole(this, 'instructor')">
                                        🧑‍🏫 Instruktur
                                    </div>
                                </div>
                                <input type="hidden" id="selected-role" name="role" value="student">
                            </div>

                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Common Fields --}}
                            <div class="form-group form-primary">
                                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                                <span class="form-bar"></span>
                                <label class="float-label">Nama Lengkap</label>
                            </div>

                            <div class="form-group form-primary">
                                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                                <span class="form-bar"></span>
                                <label class="float-label">Alamat Email</label>
                            </div>

                            {{-- Instructor-only fields --}}
                            <div id="instructor-fields" style="display: none;">
                                <div class="form-group form-primary">
                                    <input type="text" name="headline" class="form-control" value="{{ old('headline') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Psosisi/Jabatan (e.g., Web Developer)</label>
                                </div>
                                <div class="form-group form-primary">
                                    <input type="url" name="website_url" class="form-control" value="{{ old('website_url') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Website URL (Optional)</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group form-primary">
                                        <input type="password" name="password" class="form-control" required>
                                        <span class="form-bar"></span>
                                        <label class="float-label">Password</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group form-primary">
                                        <input type="password" name="password_confirmation" class="form-control" required>
                                        <span class="form-bar"></span>
                                        <label class="float-label">Konfirmasi Password</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row m-t-30">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">
                                        Daftar Sekarang
                                    </button>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <p class="text-muted">Sudah punya akun?<a href="{{ route('login') }}" class="text-primary">Masuk</a></p>
                            </div>

                            <hr/>

                            <div class="row">
                                <div class="col-md-10">
                                    <p class="text-inverse text-left m-b-0">Terima Kasih</p>
                                    <p class="text-inverse text-left"><a href="{{ route('home') }}"><b>Kembali ke website</b></a></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<style>
    html,
    body {
        height: 100%;
        margin: 0;
        overflow: hidden;
    }

    .shift-up {
        transform: translateY(-30px);
    }
</style>
<script>
    function selectRole(element, role) {
        // --- Button Visuals ---
        document.querySelectorAll('.role-btn').forEach(btn => {
            btn.classList.remove('btn-primary', 'active');
            btn.classList.add('btn-outline-secondary');
        });
        element.classList.add('btn-primary', 'active');
        element.classList.remove('btn-outline-secondary');

        // --- Form Logic ---
        document.getElementById('selected-role').value = role;
        const instructorFields = document.getElementById('instructor-fields');
        const headlineInput = document.querySelector('input[name="headline"]');
        const websiteInput = document.querySelector('input[name="website_url"]');

        if (role === 'instructor') {
            instructorFields.style.display = 'block';
            headlineInput.required = true;
            websiteInput.required = true;
        } else {
            instructorFields.style.display = 'none';
            headlineInput.required = false;
            websiteInput.required = false;
        }
    }

    // Ensure the correct fields are shown if there's an old('role') value from a validation error
    document.addEventListener('DOMContentLoaded', function() {
        const initialRole = '{{ old("role", "student") }}';
        const instructorButton = document.querySelector('.role-btn[onclick*="instructor"]');
        const studentButton = document.querySelector('.role-btn[onclick*="student"]');
        
        if (initialRole === 'instructor') {
            selectRole(instructorButton, 'instructor');
        } else {
            selectRole(studentButton, 'student');
        }
    });
</script>
@endpush