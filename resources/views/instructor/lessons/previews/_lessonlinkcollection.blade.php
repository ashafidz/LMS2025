{{-- File ini hanya berisi konten pratinjau untuk pelajaran kumpulan link --}}

<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<p>Berikut adalah daftar tautan referensi untuk pelajaran ini. Klik untuk membuka di tab baru.</p>
<hr>

@if($lesson->lessonable->links && count($lesson->lessonable->links) > 0)
    <div class="list-group">
        @foreach ($lesson->lessonable->links as $link)
            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="list-group-item list-group-item-action">
                <i class="fa fa-link mr-2"></i> {{ $link['title'] }}
                <br>
                <small class="text-muted">{{ $link['url'] }}</small>
            </a>
        @endforeach
    </div>
@else
    <p class="text-muted text-center">Tidak ada tautan yang ditambahkan untuk pelajaran ini.</p>
@endif