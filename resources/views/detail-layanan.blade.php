@extends('layouts.home-layout')

@section('title', 'Detail Pelayanan')

@section('content')
<div class="rs-page">
    {{-- Section Title --}}
    <section class="rs-pagehead">
        <div class="rs-wrap">
            <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / Detail Pelayanan</p>
            <h1 class="rs-h1">Detail Pelayanan</h1>
            <p class="rs-lead">Berikut adalah berbagai layanan yang kami sediakan untuk mendukung pengalaman belajar Anda.</p>
        </div>
    </section>

    <section class="rs-section">
        <div class="rs-wrap">
            {{-- Layanan 1: Pelatihan Interaktif --}}
            <div id="pelatihan-interaktif" class="rs-split">
                <div>
                    <h2 class="rs-h2">Pelatihan Interaktif</h2>
                    <p>
                        Belajar jadi lebih seru dengan materi interaktif yang mudah dipahami.
                        Setiap topik dilengkapi contoh nyata, latihan praktis, serta simulasi langsung sehingga peserta
                        bisa menerapkan pengetahuan yang baru diperoleh dengan segera.
                        Dengan pendekatan ini, proses belajar menjadi lebih hidup dan tidak membosankan.
                    </p>
                </div>
                <div>
                    <img src="{{ asset('images/layanan/pelatihan-interaktif.jpg') }}" alt="Seorang pengajar menjelaskan peta materi kepada tiga peserta di meja belajar yang penuh buku" width="1200" height="805" loading="lazy">
                </div>
            </div>

            {{-- Layanan 2: Kelas Beragam --}}
            <div id="kelas-beragam" class="rs-split rs-split--flip">
                <div>
                    <h2 class="rs-h2">Kelas Beragam</h2>
                    <p>
                        Tersedia ratusan kelas dalam berbagai bidang, mulai dari pemrograman, desain grafis,
                        pemasaran digital, hingga pengembangan diri.
                        Setiap kelas dirancang oleh instruktur berpengalaman agar peserta bisa belajar sesuai minat
                        dan kebutuhan mereka.
                        Dengan pilihan yang beragam, Anda dapat menyesuaikan perjalanan belajar sesuai tujuan pribadi atau karier.
                    </p>
                </div>
                <div>
                    <img src="{{ asset('images/layanan/kelas-beragam.jpg') }}" alt="Seorang peserta belajar di laptop di depan rak buku, dengan jadwal belajar di papan" width="1200" height="805" loading="lazy">
                </div>
            </div>

            {{-- Layanan 3: Materi Up-to-Date --}}
            <div id="materi-up-to-date" class="rs-split">
                <div>
                    <h2 class="rs-h2">Materi Up-to-Date</h2>
                    <p>
                        Kami selalu memperbarui materi pembelajaran agar tetap relevan dengan perkembangan industri.
                        Konten disusun berdasarkan tren terbaru, standar internasional, serta kebutuhan pasar kerja.
                        Dengan begitu, peserta tidak hanya mendapatkan ilmu teoritis, tetapi juga pengetahuan
                        yang siap diterapkan di dunia nyata.
                        Hal ini memastikan bahwa setiap peserta selalu selangkah lebih maju dalam menghadapi tantangan profesional.
                    </p>
                </div>
                <div>
                    <img src="{{ asset('images/layanan/materi-terbaru.jpg') }}" alt="Seorang peserta belajar di laptop dengan daftar bacaan dan grafik kemajuan belajar" width="1200" height="670" loading="lazy">
                </div>
            </div>

            {{-- Layanan 4: Evaluasi & Sertifikat --}}
            <div id="evaluasi-sertifikat" class="rs-split rs-split--flip">
                <div>
                    <h2 class="rs-h2">Evaluasi &amp; Sertifikat</h2>
                    <p>
                        Setiap peserta akan mendapatkan evaluasi berupa kuis, tugas, atau proyek untuk mengukur pemahaman.
                        Setelah menyelesaikan kelas, peserta berhak menerima sertifikat resmi sebagai bukti pencapaian.
                        Sertifikat ini bisa dijadikan portofolio untuk melamar pekerjaan, meningkatkan kredibilitas,
                        atau sekadar menunjukkan pencapaian pribadi.
                        Dengan adanya evaluasi, peserta dapat mengetahui kekuatan dan area yang perlu ditingkatkan.
                    </p>
                </div>
                <div>
                    <img src="{{ asset('images/layanan/evaluasi-sertifikat.jpg') }}" alt="Sertifikat kelulusan tampil di tablet, di samping tumpukan buku dan topi wisuda" width="1200" height="746" loading="lazy">
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
