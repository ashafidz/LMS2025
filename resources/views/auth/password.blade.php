@extends('layouts.home-layout')

@section('content')
    <div class="pcoded-content">
        <div class="pcoded-inner-content px-3 mt-4">
            <div class="row g-4 px-2">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Ganti Password</h5>
                        </div>
                        <div class="card-block">
                            @if (session('status') == 'password-updated')
                                <div class="alert alert-success">
                                    Password updated successfully.
                                </div>
                            @endif
                            <form method="POST" action="{{ route('user-password.update') }}">
                                @csrf
                                @method('PUT')

                                <!-- Current Password -->
                                <div class="form-group">
                                    <label for="current_password">Password Lama</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password"
                                            name="current_password" required autocomplete="current-password">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="togglePassword('current_password', this)">
                                                <i class="fa fa-eye-slash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('current_password', 'updatePassword')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div class="form-group">
                                    <label for="password">Password Baru</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password"
                                            required autocomplete="new-password">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="togglePassword('password', this)">
                                                <i class="fa fa-eye-slash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('password', 'updatePassword')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm New Password -->
                                <div class="form-group">
                                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation" required autocomplete="new-password">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="togglePassword('password_confirmation', this)">
                                                <i class="fa fa-eye-slash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('password_confirmation', 'updatePassword')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tombol Simpan -->
                                <div class="mt-4 text-right">
                                    <a href="{{ route('user.profile.index') }}" class="btn btn-outline-secondary btn-sm">Batal</a>
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword(id, button) {
            var input = document.getElementById(id);
            var icon = button.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
@endpush
