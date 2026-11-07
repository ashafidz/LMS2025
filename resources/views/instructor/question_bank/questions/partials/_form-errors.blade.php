@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Pertanyaan belum tersimpan.</strong> Perbaiki hal berikut:
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
