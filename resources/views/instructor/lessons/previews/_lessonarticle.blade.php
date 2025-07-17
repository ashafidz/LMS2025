{{-- File ini hanya berisi konten pratinjau untuk pelajaran artikel --}}

{{-- Menampilkan judul pelajaran di dalam modal --}}
<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

{{-- Menampilkan konten artikel --}}
{{-- Menggunakan {!! !!} agar tag HTML seperti <p>, <b>, dll. bisa dirender dengan benar --}}
<div class="article-content">
    {!! $lesson->lessonable->content !!}
</div>

{{-- Anda bisa menambahkan styling di sini jika perlu --}}
<style>
    .article-content {
        line-height: 1.6;
        font-size: 16px;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
</style>