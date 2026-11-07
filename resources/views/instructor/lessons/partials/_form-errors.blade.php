@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>{{ $title ?? 'Pelajaran belum tersimpan.' }}</strong> Perbaiki hal berikut:
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
        @if ($errors->hasAny(['video_file', 'document_file']))
            <p class="mb-0 mt-2">Pilih ulang file, karena peramban tidak menyimpan file yang sudah dipilih.</p>
        @endif
    </div>
@endif
