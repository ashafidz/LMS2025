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
                    <img src="{{ asset('images/side-images/annie-unsplash.jpg') }}" alt="Login Illustration"
                        style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                </div>
            </div>

            <!-- KANAN: Form Login -->
            <div class="col-md-6 align-items-center d-flex" style="min-height: 100vh;">
                <form class="md-float-material form-material w-100 px-4" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="auth-box card">
                        <div class="card-block">

                            <div class="text-center mb-3">
                                <h3 class="text-center">Masuk</h3>
                                <p class="font-weight-bold">Masuk Sebagai</p>
                                <div class="d-flex justify-content-center mb-2">
                                    <div class="role-btn btn btn-primary mr-2 active"
                                        onclick="selectRole(this, 'student')">
                                        👨‍🎓 Siswa
                                    </div>
                                    <div class="role-btn btn btn-outline-secondary"
                                        onclick="selectRole(this, 'instructor')">
                                        🧑‍🏫 Instruktur
                                    </div>
                                </div>
                                <input type="hidden" id="selected-role" name="login_preference" value="student">
                            </div>

                            <input type="hidden" name="timezone" id="user_timezone">

                            <!-- Validation Errors -->
                             @if ($errors->any())
                                <div class="alert alert-danger">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="form-group form-primary">
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required placeholder="" value="{{ old('email') }}">
                                <span class="form-bar"></span>
                                <label class="float-label">Alamat Email</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group form-primary">
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="">
                                <span class="form-bar"></span>
                                <label class="float-label">Password</label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mt-2 mb-3">
                                <div class="col text-left">
                                    <div class="checkbox-fade fade-in-primary">
                                        <label>
                                            <input type="checkbox" name="remember">
                                            <span class="cr">
                                                <i class="cr-icon icofont icofont-ui-check txt-primary"></i>
                                            </span>
                                            <span class="text-inverse">Ingatkan Saya</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col text-right">
                                     @if (Route::has('password.request'))
                                        <a href="#" data-toggle="modal" data-target="#forgotModal" class="f-w-600">Lupa Password?</a>
                                    @endif
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button type="submit"
                                        class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">
                                        Log In
                                    </button>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <p class="text-muted">Belum punya akun?<a href="{{ route('register') }}"
                                        class="text-primary">Daftar</a></p>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-10">
                                    <p class="text-inverse text-left m-b-0">Terima Kasih</p>
                                    <p class="text-inverse text-left"><a href="{{ route('home') }}"><b>Kembali ke website</b></a>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>


{{-- modal forgot password --}}
<div class="modal fade" id="forgotModal" tabindex="-1" role="dialog" aria-labelledby="forgotModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content rounded">
      <div class="modal-header">
        <h5 class="modal-title" id="forgotModalLabel">🔐 Lupa Kata Sandi Anda?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="mb-4 text-sm text-muted">
            {{ __('Tidak masalah. Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.') }}
        </div>

        @if (session('status'))
            <div class="alert alert-success mb-4" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus />
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-dark">
                    {{ __('Kirim Tautan Reset Kata Sandi') }}
                </button>
            </div>
        </form>
        
      </div>
    </div>
  </div>
</div>
{{-- end modal forgot password --}}
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
        document.getElementById('user_timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
    </script>
    <script>
        function selectRole(element, role) {
            // Remove 'active' from all buttons and add 'btn-outline-secondary'
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.classList.remove('btn-primary', 'active');
                btn.classList.add('btn-outline-secondary');
            });

            // Add 'active' to the clicked button and remove 'btn-outline-secondary'
            element.classList.add('btn-primary', 'active');
            element.classList.remove('btn-outline-secondary');

            // Set the value of the hidden input
            document.getElementById('selected-role').value = role;
        }
    </script>
@endpush