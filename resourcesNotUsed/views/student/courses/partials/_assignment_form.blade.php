{{-- resources/views/student/courses/partials/_assignment_form.blade.php --}}

<h5 class="font-weight-bold mt-4">Kumpulkan Tugas Anda</h5>
<form action="{{ route('student.assignment.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="submission_file">Pilih File (PDF atau ZIP)</label>
        <input type="file" name="submission_file" class="form-control" required accept=".pdf,.zip">
        <small class="form-text text-muted">Ukuran file maksimal: 20MB.</small>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fa fa-upload"></i> Kirim Tugas
    </button>
</form>