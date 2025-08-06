@extends('layouts.app-layout')

@section('content')
<section class="login-block">
    <div class="container-fluid px-0">
        <div class="row no-gutters shift-up" style="height: 100vh; overflow: hidden;">

            <!-- KIRI: Gambar dalam kotak biru -->
            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center bg-primary"
                style="min-height: 100vh;">
                <div
                    style="width: 620px; height: 620px; background-color: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    <img src="{{ asset('images/side-images/studio-unsplash.jpg') }}" alt="Registration Illustration"
                        style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                </div>
            </div>

            <!-- KANAN: Form Registrasi -->
            <div class="col-md-6 align-items-center d-flex" style="min-height: 100vh;">
                <form class="md-float-material form-material w-100 px-4" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="auth-box card">
                        <div class="card-block">

                            <div class="text-center mb-3">
                                <h3 class="text-center">Sign Up</h3>
                                <p class="font-weight-bold">Register As</p>
                                <div class="d-flex justify-content-center mb-2">
                                    <div class="role-btn btn btn-primary mr-2 active" onclick="selectRole(this, 'student')">
                                        👨‍🎓 Student
                                    </div>
                                    <div class="role-btn btn btn-outline-secondary" onclick="selectRole(this, 'instructor')">
                                        🧑‍🏫 Instructor
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
                                <label class="float-label">Full Name</label>
                            </div>

                            <div class="form-group form-primary">
                                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                                <span class="form-bar"></span>
                                <label class="float-label">Email Address</label>
                            </div>

                            {{-- Instructor-only fields --}}
                            <div id="instructor-fields" style="display: none;">
                                <div class="form-group form-primary">
                                    <input type="text" name="headline" class="form-control" value="{{ old('headline') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Headline (e.g., Web Developer)</label>
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
                                        <label class="float-label">Confirm Password</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row m-t-30">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">
                                        Sign up now
                                    </button>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <p class="text-muted">Already have an account? <a href="{{ route('login') }}" class="text-primary">Log in</a></p>
                            </div>

                            <hr/>

                            <div class="row">
                                <div class="col-md-10">
                                    <p class="text-inverse text-left m-b-0">Thank You</p>
                                    <p class="text-inverse text-left"><a href="{{ route('home') }}"><b>Back to website</b></a></p>
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