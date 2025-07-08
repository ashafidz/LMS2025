@extends('layouts.app-layout')

@section('content')

    <section class="login-block">
        <!-- Container-fluid starts -->
        <div class="container">
            <div class="row">
                <div class="col-sm-12">


                    {{-- <div class="text-center mb-3">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="login_preference" id="login_as_student" value="student" checked>
                            <label class="btn btn-outline-primary" for="login_as_student">🎓 Student</label>
                            <input type="radio" class="btn-check" name="login_preference" id="login_as_instructor" value="instructor">
                            <label class="btn btn-outline-primary" for="login_as_instructor">🧑‍🏫 Instructor</label>
                        </div>
                    </div> --}}




                    <form method="POST" action="{{ route('login') }}" class="md-float-material form-material">
                        @csrf
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                        <div class="col-md-12">
                                            <h3 class="text-center">Sign In</h3>
                                        </div>
                                    </div>
                                <!-- Authentication card start -->
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <!-- Role Dropdown -->
                                <div class="form-group form-primary">
                                    {{-- <label class="float-label" for="login_preference">Login as</label> --}}
                                    <select id="login_preference" name="login_preference" class="form-control" required>
                                        <option value="student"
                                            {{ old('login_preference') == 'student' ? 'selected' : '' }}>🎓 Student
                                        </option>
                                        <option value="instructor"
                                            {{ old('login_preference') == 'instructor' ? 'selected' : '' }}>🧑‍🏫
                                            Instructor</option>
                                    </select>
                                    <span class="form-bar"></span>
                                </div>
                                <!-- Email input -->
                                <div class="form-group form-primary">
                                    <input type="email" name="email" class="form-control" required
                                        value="{{ old('email') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Your Email Address</label>
                                </div>

                                <!-- Password input -->
                                <div class="form-group form-primary">
                                    <input type="password" name="password" class="form-control" required>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Password</label>
                                </div>

                                <div class="row m-t-25 text-left">
                                    <div class="col-12">


                                        {{-- <div class="checkbox-fade fade-in-primary d-">
                                            <label for="remember_me">
                                                <input type="checkbox" id="remember_me" name="remember">
                                                <span class="cr"><i
                                                        class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                <span class="text-inverse">Remember me</span>
                                            </label>
                                        </div> --}}

                                        <div class="checkbox-fade fade-in-primary">
                                            <label>
                                                <input type="checkbox" id="remember_me" name="remember">
                                                <span class="cr">
                                                    <i class="cr-icon icofont icofont-ui-check txt-primary"></i>
                                                </span>
                                                <span class="text-inverse">Remember me</span>
                                            </label>
                                        </div>



                                        @if (Route::has('password.request'))
                                            <div class="forgot-phone text-right f-right">
                                                <a href="{{ route('password.request') }}" class="text-right f-w-600"> Forgot
                                                    Password?</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>


                                <!-- Submit -->
                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit"
                                            class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20">
                                            {{ __('Sign in') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- end of form -->
                </div>
                <!-- end of col-sm-12 -->
            </div>
            <!-- end of row -->
        </div>
        <!-- end of container-fluid -->
    </section>
@endsection

{{-- CHANGE 3: A smarter script to handle both forms --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Find all role-switching radio buttons on the page
        const roleRadios = document.querySelectorAll('.btn-check[name^="login_as_"]');

        roleRadios.forEach((radio) => {
            radio.addEventListener("change", function(event) {
                // Get the form that this radio button is inside
                const form = event.target.closest('form');
                if (form) {
                    // Find the hidden input *within that specific form*
                    const hiddenInput = form.querySelector('.login-preference-input');
                    if (hiddenInput) {
                        hiddenInput.value = event.target.value;
                    }
                }
            });
        });
    });
</script>

{{-- Script to auto-open modal (this part is still correct) --}}
@if ((session('status') && str_contains(session('status'), 'link')) || ($errors->any() && old('is_forgot_password')))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var forgotPasswordModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            forgotPasswordModal.show();
        });
    </script>
@endif
