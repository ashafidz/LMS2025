@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">
                                Bank Soal
                            </h5>
                            <p class="m-b-0">
                                Edit Materi Bank Soal
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.question-bank.topics.index') }}">Bank Soal</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#!">Edit Materi</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page-header end -->
        <div class="pcoded-inner-content">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page-body start -->
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <!-- Basic Form Inputs card start -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Edit Materi</h5>
                                        <span>Perbarui informasi tentang materi ini</span>
                                    </div>
                                    <div class="card-block">
                                        <h4 class="sub-title">Form Edit Materi</h4>
                                        <form action="{{ route('instructor.question-bank.topics.update', $topic->id) }}"
                                            method="POST"
                                            >
                                            @csrf
                                            @method('PUT') {{-- Use PUT method for updates --}}

                                            {{-- Topic Name Field --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Nama Materi</label>
                                                <div class="col-sm-10">
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                        placeholder="(contoh: Materi PHP Dasar)"
                                                        id="name"
                                                        name="name"
                                                        value="{{ old('name', $topic->name) }}"
                                                        required
                                                        >
                                                        @error('name')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                </div>
                                            </div>
                                            {{-- Description Field --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Description (Optional)</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" cols="5" class="form-control @error('description') is-invalid @enderror" placeholder="Berikan deskripsi tentang materi ini" id="description" name="description">{{ old('description', $topic->description) }}</textarea>
                                                </div>
                                                @error('description')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="d-flex">
                                                <a href="{{ route('instructor.question-bank.topics.index') }}" class="btn btn-danger mr-3">
                                                    <i class="icofont icofont-close"></i>
                                                    Batal
                                                </a>
                                                <button class="btn btn-primary" type="submit" >
                                                    <i class="icofont icofont-save"></i>
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
                <div id="styleSelector"></div>
            </div>
        </div>
    </div>
@endsection