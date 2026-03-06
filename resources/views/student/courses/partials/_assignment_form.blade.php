{{-- resources/views/student/courses/partials/_assignment_form.blade.php --}}

<form action="{{ route('student.assignment.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group mb-4">
        <label for="submission_file" class="font-weight-bold text-dark mb-2">Pilih File Tugas (PDF atau ZIP)</label>
        <div class="custom-file">
            <input type="file" name="submission_file" class="custom-file-input" id="submission_file" required accept=".pdf,.zip">
            <label class="custom-file-label" for="submission_file">Choose file...</label>
        </div>
        <small class="form-text text-muted mt-2"><i class="fa fa-info-circle mr-1"></i>Ukuran file maksimal: 20MB.</small>
    </div>
    
    <button type="submit" class="btn btn-primary btn-block shadow-sm font-weight-bold py-2">
        <i class="fa fa-paper-plane mr-2"></i> Kirim Tugas
    </button>
</form>

@push('scripts')
<script>
    // To make the custom-file-input show the selected file name
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("submission_file").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>
@endpush