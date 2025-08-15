{{-- File ini hanya berisi konten pratinjau untuk pelajaran artikel --}}
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

    /* === TAMBAHKAN KODE INI === */
    .article-content ul {
        list-style-type: disc; /* Mengembalikan gaya bullet point (bulatan) */
        padding-left: 20px;   /* Memberi padding kiri agar tidak menempel */
        margin-left: 20px;    /* Memberi margin kiri untuk inden */
    }
    .article-content ul li {
        margin-bottom: 0.5rem; /* Memberi jarak antar item list */
    }
    .article-content ol {
        list-style-type: decimal; /* Mengembalikan gaya penomoran */
        padding-left: 20px;
        margin-left: 20px;
    }
    /* === AKHIR KODE TAMBAHAN === */
</style>


{{-- Menampilkan judul pelajaran di dalam modal --}}
<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

{{-- Menampilkan konten artikel --}}
{{-- Menggunakan {!! !!} agar tag HTML seperti <p>, <b>, dll. bisa dirender dengan benar --}}
<div class="article-content">
    {!! $lesson->lessonable->content !!}
</div>

{{-- Anda bisa menambahkan styling di sini jika perlu --}}
{{-- <style>
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
</style> --}}