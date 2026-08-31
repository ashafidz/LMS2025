{{-- resources/views/student/courses/partials/_assignment_form.blade.php --}}

<style>
.upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    background-color: #f8fafc;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    margin-bottom: 1rem;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}
.upload-zone i.upload-icon {
    font-size: 3.5rem;
    color: #94a3b8;
    margin-bottom: 1rem;
    transition: color 0.3s;
}
.upload-zone:hover i.upload-icon, .upload-zone.dragover i.upload-icon {
    color: #3b82f6;
}
.upload-zone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.file-preview-card {
    display: none;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    background: #ffffff;
    margin-bottom: 1rem;
    align-items: center;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}
.file-preview-card .file-icon {
    font-size: 2.5rem;
    margin-right: 1.25rem;
}
.file-preview-card .file-icon.pdf { color: #ef4444; }
.file-preview-card .file-icon.zip { color: #eab308; }
.file-preview-card .file-info {
    flex-grow: 1;
    overflow: hidden;
    text-align: left;
}
.file-preview-card .file-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.file-preview-card .file-size-text {
    font-size: 0.875rem;
    color: #64748b;
}
.file-preview-card .remove-file-btn {
    color: #ef4444;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}
.file-preview-card .remove-file-btn:hover {
    background: #fee2e2;
}
</style>

<form action="{{ route('student.assignment.submit', $assignment) }}" method="POST" enctype="multipart/form-data" class="assignment-submit-form" onsubmit="var f=this.querySelector('input[type=\'file\']'); if(f && f.files.length>0 && f.files[0].size>20*1024*1024){ alert('Ukuran file melebihi 20MB!'); return false; } if(!f || f.files.length===0){ alert('Harap pilih file terlebih dahulu.'); return false; } return true;">
    @csrf
    <div class="form-group mb-4">
        <label class="font-weight-bold text-dark mb-3" style="font-size: 1.1rem;">Unggah File Tugas (PDF atau ZIP)</label>
        
        <div class="file-size-alert-container"></div>
        
        <div class="upload-zone">
            <i class="fa fa-cloud-upload-alt upload-icon"></i>
            <h5 class="font-weight-bold text-dark mb-1">Klik atau seret file ke sini</h5>
            <p class="text-muted mb-0">Hanya mendukung format .pdf dan .zip (Maks. 20MB)</p>
            
            <input type="file" name="submission_file" required accept=".pdf,.zip" 
            onchange="
                var file = this.files[0];
                var form = this.closest('form');
                var alertContainer = form.querySelector('.file-size-alert-container');
                var submitBtn = form.querySelector('button[type=\'submit\']');
                var preview = form.querySelector('.file-preview-card');
                var uploadZone = form.querySelector('.upload-zone');
                
                if (!file) {
                    return;
                }
                
                var maxSize = 20 * 1024 * 1024;
                alertContainer.innerHTML = '';
                
                if (file.size > maxSize) {
                    alertContainer.innerHTML = '<div class=\'alert alert-danger alert-dismissible fade show\' role=\'alert\'><strong>Peringatan!</strong> Ukuran file melebihi batas maksimal 20MB. Silakan pilih file yang lebih kecil.<button type=\'button\' class=\'close\' data-dismiss=\'alert\' aria-label=\'Close\'><span aria-hidden=\'true\'>&times;</span></button></div>';
                    this.value = '';
                    preview.style.display = 'none';
                    uploadZone.style.display = 'block';
                    submitBtn.disabled = true;
                    return;
                }
                
                form.querySelector('.file-name').innerText = file.name;
                var sizeMB = (file.size / (1024*1024)).toFixed(2);
                form.querySelector('.file-size-text').innerText = sizeMB + ' MB';
                
                var iconEl = form.querySelector('.file-icon');
                if(file.name.toLowerCase().endsWith('.zip')) {
                    iconEl.innerHTML = '<i class=\'fa fa-file-archive\'></i>';
                    iconEl.className = 'file-icon zip';
                } else {
                    iconEl.innerHTML = '<i class=\'fa fa-file-pdf\'></i>';
                    iconEl.className = 'file-icon pdf';
                }
                
                uploadZone.style.display = 'none';
                preview.style.display = 'flex';
                submitBtn.disabled = false;
            "
            ondragover="this.parentElement.classList.add('dragover')"
            ondragleave="this.parentElement.classList.remove('dragover')"
            ondrop="this.parentElement.classList.remove('dragover')">
        </div>
        
        <div class="file-preview-card">
            <div class="file-icon pdf">
                <i class="fa fa-file-pdf"></i>
            </div>
            <div class="file-info">
                <div class="file-name">document.pdf</div>
                <div class="file-size-text">2.5 MB</div>
            </div>
            <div class="remove-file-btn" title="Hapus file" onclick="
                var form = this.closest('form');
                form.querySelector('input[type=\'file\']').value = '';
                form.querySelector('.file-preview-card').style.display = 'none';
                form.querySelector('.upload-zone').style.display = 'block';
                form.querySelector('.file-size-alert-container').innerHTML = '';
                form.querySelector('button[type=\'submit\']').disabled = false;
            ">
                <i class="fa fa-trash"></i>
            </div>
        </div>
        
    </div>
    
    <button type="submit" class="btn btn-primary btn-block shadow-sm font-weight-bold py-3 submit-assignment-btn" style="border-radius: 10px; font-size: 1.05rem;">
        <i class="fa fa-paper-plane mr-2"></i> Kirim Tugas Sekarang
    </button>
</form>