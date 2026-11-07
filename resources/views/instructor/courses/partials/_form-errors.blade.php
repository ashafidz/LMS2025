@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Kursus belum tersimpan.</strong> Perbaiki hal berikut:
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
        @if ($errors->has('thumbnail'))
            <p class="mb-0 mt-2">Pilih ulang gambar thumbnail, karena peramban tidak menyimpan file yang sudah dipilih.</p>
        @endif
    </div>
@endif
