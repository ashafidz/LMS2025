@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content">
    <div class="page-header">
        {{-- ... (header sama seperti halaman edit lainnya) ... --}}
    </div>
    <div class="pcoded-inner-content">
        <div class="main-body"><div class="page-wrapper"><div class="page-body">
            <div class="row"><div class="col-sm-12">
                <div class="card">
                    <div class="card-header"><h5>Edit Detail Sesi Poin</h5></div>
                    <div class="card-block">
                        <form action="{{ route('instructor.lessons.update', $lesson->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                <div class="col-sm-10">
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Judul Sesi</label>
                                <div class="col-sm-10">
                                    <input type="text" name="lessonpoin_title" class="form-control" value="{{ old('lessonpoin_title', $lesson->lessonable->title) }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Deskripsi (Opsional)</label>
                                <div class="col-sm-10">
                                    <textarea name="lessonpoin_description" class="form-control" rows="4">{{ old('lessonpoin_description', $lesson->lessonable->description) }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div></div>
        </div></div></div>
    </div>
</div>
@endsection