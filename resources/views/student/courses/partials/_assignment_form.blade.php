{{-- resources/views/student/courses/partials/_assignment_form.blade.php --}}

<form action="{{ route('student.assignment.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group mb-4">
        <label for="submission_file" class="font-weight-bold text-dark mb-2">Pilih File Tugas (PDF atau ZIP)</label>
        
        <div id="file-size-alert-container"></div>
        
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
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileInput = e.target;
        var file = fileInput.files[0];
        if (!file) return;

        var fileName = file.name;
        var nextSibling = fileInput.nextElementSibling;
        nextSibling.innerText = fileName;

        var maxSize = 20 * 1024 * 1024; // 20MB in bytes
        var alertContainer = document.getElementById('file-size-alert-container');
        alertContainer.innerHTML = ''; // Clear existing alerts

        if (file.size > maxSize) {
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Peringatan!</strong> Ukuran file melebihi batas maksimal 20MB. Silakan pilih file yang lebih kecil.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            // Reset input
            fileInput.value = '';
            nextSibling.innerText = 'Choose file...';
        }
    });
</script>
@endpush