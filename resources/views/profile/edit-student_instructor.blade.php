@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <div class="pcoded-inner-content px-3" style="padding-top: 32px;">
            <div class="row g-4 px-2">

                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data"
                    class="row col-12">
                    @csrf
                    @method('PUT')

                    <!-- Avatar & Aksi -->
                    <div class="col-xl-4 col-md-12 mb-4">
                        <div class="card text-center">
                            <div class="card-body py-4">
                                <h6 class="mb-3">Foto Profil</h6>
                                <img class="rounded-circle mr-4" style="width: 100px; height: 100px; border: 3px;"
                            src="{{ asset(Auth::user()->profile_picture_url ?? 'https://placehold.co/32x32/EBF4FF/767676?text=SA') }}" alt="User-Profile-Image">
                                <input type="file" name="profile_picture" class="form-control-file">
                                @error('profile_picture')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Edit Profil -->
                    <div class="col-xl-8 col-md-12 mb-4">
                        <div class="card ">
                            <div class="card-header">
                                <h5>Edit Profil</h5>
                            </div>
                            <div class="card-block">
                                <!-- Biodata -->
                                <div class="form-group">
                                    <label for="name">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name', $user->name) }}">
                                    @error('name')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone_number">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                                        value="{{ old('phone_number', $user->phone_number) }}">
                                    @error('phone_number')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="birth_date">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date"
                                        value="{{ old('birth_date', $user->birth_date) }}">
                                    @error('birth_date')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="gender">Jenis Kelamin</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="">Pilih</option>
                                        <option value="male"
                                            {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>
                                            Laki-laki</option>
                                        <option value="female"
                                            {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>
                                            Perempuan</option>
                                    </select>
                                    @error('gender')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="address">Alamat Lengkap</label>
                                    <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="headline">Headline</label>
                                    <input type="text" class="form-control" id="headline" name="headline"
                                        value="{{ old('headline', $profile->headline ?? '') }}">
                                    @error('headline')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3">{{ old('bio', $profile->bio ?? '') }}</textarea>
                                    @error('bio')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="website_url">Website</label>
                                    <input type="url" class="form-control" id="website_url" name="website_url"
                                        value="{{ old('website_url', $profile->website_url ?? '') }}">
                                    @error('website_url')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Pendidikan -->
                                <hr>
                                <h6 class="text-primary font-weight-bold mt-4">Data Profesional</h6>

                                <div class="form-group">
                                    <label for="highest_level_of_education">Pendidikan Terakhir</label>
                                    <input type="text" class="form-control" id="highest_level_of_education"
                                        name="highest_level_of_education"
                                        value="{{ old('highest_level_of_education', $profile->highest_level_of_education ?? '') }}">
                                    @error('highest_level_of_education')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="profession">Pekerjaan / Profesi </label>
                                    <input type="text" class="form-control" id="profession" name="profession"
                                        value="{{ old('profession', $profile->profession ?? '') }}">
                                    @error('profession')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="company_or_institution_name">Nama Perusahaan / Institusi Saat Ini</label>
                                    <input type="text" class="form-control" id="company_or_institution_name"
                                        name="company_or_institution_name"
                                        value="{{ old('company_or_institution_name', $profile->company_or_institution_name ?? '') }}">
                                    @error('company_or_institution_name')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="company_address">Alamat Perusahaan</label>
                                    <textarea class="form-control" id="company_address" name="company_address" rows="2">{{ old('company_address', $profile->company_address ?? '') }}</textarea>
                                    @error('company_address')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="company_tax_id">No. NPWP Perusahaan</label>
                                    <input type="text" class="form-control" id="company_tax_id" name="company_tax_id"
                                        value="{{ old('company_tax_id', $profile->company_tax_id ?? '') }}">
                                    @error('company_tax_id')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tombol -->
                                <div class="mt-4 text-right">
                                    <a href="{{ route('user.profile.index') }}"
                                        class="btn btn-outline-secondary btn-sm">Batal</a>
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
