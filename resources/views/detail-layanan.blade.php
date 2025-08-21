@extends('layouts.home-layout')

@section('title', 'Detail Pelayanan')

@section('content')

{{-- Section Title --}}
<section class="section light-background" style="padding-top: 120px; padding-bottom:5px;">
  <div class="container section-title" data-aos="fade-up">
    <h2 class="fw-bold">Detail Pelayanan</h2>
    <p>Berikut adalah berbagai layanan yang kami sediakan untuk mendukung pengalaman belajar Anda.</p>
  </div>
</section>

{{-- Layanan 1: Pelatihan Interaktif --}}
<section id="pelatihan-interaktif" class="section bg-white">
  <div class="container">
    <div class="row align-items-center" data-aos="fade-up">
      <div class="col-lg-6">
        <h3 class="fw-bold">Pelatihan Interaktif</h3>
        <p>
          Belajar jadi lebih seru dengan materi interaktif yang mudah dipahami.  
          Setiap topik dilengkapi contoh nyata, latihan praktis, serta simulasi langsung sehingga peserta 
          bisa menerapkan pengetahuan yang baru diperoleh dengan segera.  
          Dengan pendekatan ini, proses belajar menjadi lebih hidup dan tidak membosankan.
        </p>
      </div>
      <div class="col-lg-6 text-center">
        <img src="{{ asset('images/layanan1.jpg') }}" class="img-fluid w-75 rounded shadow" alt="Pelatihan Interaktif">
      </div>
    </div>
  </div>
</section>

{{-- Layanan 2: Kelas Beragam --}}
<section id="kelas-beragam" class="section light-background">
  <div class="container">
    <div class="row align-items-center" data-aos="fade-up">
      <div class="col-lg-6 order-lg-2">
        <h3 class="fw-bold">Kelas Beragam</h3>
        <p>
          Tersedia ratusan kelas dalam berbagai bidang, mulai dari pemrograman, desain grafis, 
          pemasaran digital, hingga pengembangan diri.  
          Setiap kelas dirancang oleh instruktur berpengalaman agar peserta bisa belajar sesuai minat 
          dan kebutuhan mereka.  
          Dengan pilihan yang beragam, Anda dapat menyesuaikan perjalanan belajar sesuai tujuan pribadi atau karier.
        </p>
      </div>
      <div class="col-lg-6 order-lg-1 text-center">
        <img src="{{ asset('images/layanan2.jpg') }}" class="img-fluid w-75 rounded shadow" alt="Kelas Beragam">
      </div>
    </div>
  </div>
</section>

{{-- Layanan 3: Materi Up-to-Date --}}
<section id="materi-up-to-date" class="section bg-white">
  <div class="container">
    <div class="row align-items-center" data-aos="fade-up">
      <div class="col-lg-6">
        <h3 class="fw-bold">Materi Up-to-Date</h3>
        <p>
          Kami selalu memperbarui materi pembelajaran agar tetap relevan dengan perkembangan industri.  
          Konten disusun berdasarkan tren terbaru, standar internasional, serta kebutuhan pasar kerja.  
          Dengan begitu, peserta tidak hanya mendapatkan ilmu teoritis, tetapi juga pengetahuan 
          yang siap diterapkan di dunia nyata.  
          Hal ini memastikan bahwa setiap peserta selalu selangkah lebih maju dalam menghadapi tantangan profesional.
        </p>
      </div>
      <div class="col-lg-6 text-center">
        <img src="{{ asset('images/layanan3.jpg') }}" class="img-fluid w-75 rounded shadow" alt="Materi Up-to-Date">
      </div>
    </div>
  </div>
</section>

{{-- Layanan 4: Evaluasi & Sertifikat --}}
<section id="evaluasi-sertifikat" class="section light-background">
  <div class="container">
    <div class="row align-items-center" data-aos="fade-up">
      <div class="col-lg-6 order-lg-2">
        <h3 class="fw-bold">Evaluasi & Sertifikat</h3>
        <p>
          Setiap peserta akan mendapatkan evaluasi berupa kuis, tugas, atau proyek untuk mengukur pemahaman.  
          Setelah menyelesaikan kelas, peserta berhak menerima sertifikat resmi sebagai bukti pencapaian.  
          Sertifikat ini bisa dijadikan portofolio untuk melamar pekerjaan, meningkatkan kredibilitas, 
          atau sekadar menunjukkan pencapaian pribadi.  
          Dengan adanya evaluasi, peserta dapat mengetahui kekuatan dan area yang perlu ditingkatkan.
        </p>
      </div>
      <div class="col-lg-6 order-lg-1 text-center">
        <img src="{{ asset('images/layanan5.jpg') }}" class="img-fluid w-75 rounded shadow" alt="Evaluasi & Sertifikat">
      </div>
    </div>
  </div>
</section>

@endsection
