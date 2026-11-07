# ADR-011: Teks kaya (rich text) untuk soal kuis

- Status: Diterima
- Tanggal: 2026-11-05
- Rilis: 2.0.0

## Konteks

Pemilik minta teks soal, penjelasan, dan pilihan jawaban (pilihan ganda) bisa berformat dan memuat gambar.
Kolom `questions.question_text`, `questions.explanation`, dan `question_options.option_text` berisi teks polos,
dan produksi punya banyak soal tersimpan. Semua halaman menampilkannya dengan `nl2br(e(...))`. Kalau baris lama dibaca
sebagai HTML, teks seperti "x < 5" atau "A & B" rusak dan pindah baris hilang.

## Keputusan

1. **Penanda format.** Kolom baru `questions.text_format` (`plain` atau `html`, default `plain`). Satu penanda berlaku
   untuk teks soal, penjelasan, dan (hanya untuk dua tipe pilihan ganda) teks pilihan jawaban soal itu.
2. **Baris lama tidak dikonversi.** Migrasi tidak mengubah data. Semua soal lama menjadi `plain` lewat default dan
   tampil persis seperti sebelumnya. Soal yang dipakai di kuis terkunci (tidak bisa diedit), jadi tetap `plain` selamanya.
   Soal lama yang diedit lalu disimpan lewat editor baru menjadi `html` (editor memuatnya sebagai paragraf).
3. **Tampilan lewat satu tempat.** Metode `Question::displayText()`, `displayTextWithBlanks()`, `displayExplanation()`,
   `displayOption()`, dan `plainSummary()` menentukan cara menampilkan per format. HTML selalu dibersihkan
   `HtmlSanitizer::clean()` saat disimpan dan saat ditampilkan.
4. **Gambar diunggah sebagai file**, bukan base64, ke `storage/app/public/question-images/{id instruktur}` lewat
   `POST /instructor/question-images` (maks 2 MB, JPG/PNG/GIF, 30 unggahan per menit). Alasannya: kolom `TEXT` MySQL
   maksimal 64 KB dan halaman kuis memuat semua soal sekaligus. Alamat disimpan relatif (`/storage/...`) supaya tidak
   bergantung pada `APP_URL`. Gambar base64 ditolak saat simpan.
5. **Drag and drop dan benar/salah tetap polos** untuk teks pilihannya. Pilihan drag and drop tampil di dalam `<select>`,
   dan pilihan benar/salah adalah teks tetap "Benar" dan "Salah". Teks soal dan penjelasan tipe ini tetap kaya.
   Penanda `[[BLANK_n]]` harus utuh (tanpa tebal/miring di tengahnya), dicek saat simpan.
6. **Penilaian tidak berubah.** Penilaian hanya membandingkan ID pilihan, bukan teks.

## Konsekuensi

- **Gambar yatim tidak dibersihkan.** Menghapus soal atau mengganti gambar tidak menghapus file. Menghapus file tidak aman
   karena salinan soal (clone) memakai alamat gambar yang sama. Pembersihan dan pustaka media di luar cakupan 2.0.0.
- Dua format hidup berdampingan di basis data; kode tampilan harus selalu lewat metode `Question`, bukan `{{ }}` langsung.
- Soal HTML tidak bisa dikembalikan ke teks polos.

## Pembaruan 2026-11-05: foto besar dan pesan unggah
- Foto HP (3-6 MB) ditolak oleh batas 2 MB, dan Jodit hanya menampilkan teks status HTTP ("Unprocessable Content").
- Editor kini memperkecil gambar JPG/PNG di atas 2 MB dan semua WebP di peramban (canvas, sisi terpanjang 1600 px, JPEG 0,85;
  PNG dipertahankan bila muat). Orientasi EXIF diterapkan dan data EXIF (termasuk lokasi GPS) ikut terbuang. GIF tidak diubah
  agar animasinya tetap; GIF di atas 2 MB ditolak di peramban dengan pesan jelas.
- Unggahan dikirim lewat `customUploadFunction` (fetch) agar setiap kegagalan (validasi, 413, 419, jaringan) menjadi satu pesan
  Indonesia. Endpoint membalas galat validasi dengan HTTP 200 dan `success: false`, karena Jodit hanya membaca `data.messages`
  dari balasan 2xx.
- Batas server tetap 2 MB, jadi tidak bergantung pada `upload_max_filesize` di `php.ini` produksi.
- Kode pemerkecil gambar kini berada di `public/js/rs-image-shrink.js` dan dipakai bersama oleh editor soal, thumbnail kursus
  (maks 2 MB, 1600 px), dan ikon lencana (maks 1 MB, 512 px, WebP agar transparansi tetap).

## Catatan deploy

1. `php artisan migrate` (menambah kolom `text_format`, tanpa mengubah baris yang ada).
2. `storage:link` harus sudah ada. Produksi sudah menyajikan `/storage/thumbnails`, jadi cek bahwa
   `/storage/...` memang terbuka sebelum mengandalkan unggah gambar soal.
