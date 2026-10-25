# ADR-001: Template Sertifikat Berversi dengan Editor Visual

- **Status**: Accepted
- **Date**: 2026-10-24
- **Deciders**: Tim ruangstudi.id (permintaan fitur dari superadmin)

---

## Context and Problem Statement
Sertifikat siswa dibuat dari view Blade dengan posisi teks yang ditulis langsung di kode dan satu gambar latar tetap
(`public/assets/images/certificate-background.png`). Mengubah tampilan membutuhkan developer. Superadmin dan admin
(staf sekolah/kementerian, bukan developer) perlu mengatur sendiri gambar latar dan penempatan nama, kode, dan
teks lain, dengan riwayat versi dan pilihan versi yang aktif.

---

## Decision Drivers
- Pengguna awam: atur posisi dengan menyeret, tanpa mengetik JSON atau HTML.
- Hosting bersama (batas 20 proses PHP, memori terbatas): tanpa layanan eksternal, PDF di-cache.
- Satu sumber kebenaran posisi untuk editor, pratinjau, dan PDF.
- Versi tidak boleh hilang (rollback), dan sertifikat siswa selalu memakai desain aktif terbaru.

---

## Considered Options
1. **Editor visual sendiri (jQuery UI) + tabel `certificate_templates` berversi + dompdf**: tanpa dependensi baru.
2. Library kanvas (Fabric.js/Konva): fitur kaya, tetapi menambah framework JS dan build step.
3. Template Blade yang diedit developer: tidak memenuhi kebutuhan.

---

## Decision Outcome
Chosen option: **1**, karena memakai jQuery UI yang sudah dimuat layout, tanpa build step, dan dompdf yang sudah dipakai.

Keputusan penting:
- **Versi tidak pernah ditimpa**: setiap simpan membuat baris baru dengan nomor versi naik (`max + 1` dalam transaksi
  dengan `lockForUpdate`). Tepat satu versi aktif, dijaga di `CertificateTemplateService`, atau tidak ada (desain
  bawaan lama tetap dipakai sebagai cadangan).
- **Posisi dalam persen halaman** (bukan piksel/mm), jangkar `x` mengikuti perataan (kiri/tengah/kanan). Halaman selalu
  A4 (mendatar atau tegak). Elemen disimpan sebagai JSON di kolom `elements`, hanya dibaca/ditulis kode, tidak pernah
  ditampilkan mentah ke pengguna.
- **Aktivasi tidak menulis ulang sertifikat**: PDF dibuat saat diunduh dan di-cache per versi
  (`certificates/{kode}-t{id}v{versi}-{md5}.pdf`); PDF lama sertifikat yang sama dihapus. Kode dan tanggal terbit tetap.
- **Font**: Helvetica dan Times bawaan dompdf; Montserrat (Regular + Bold, instance statis dari file variable resmi),
  Playfair Display dan Great Vibes (file resmi apa adanya, hanya Regular) dari repo google/fonts, lisensi SIL OFL
  (`public/fonts/certificates/OFL.txt`). Bold/italic dimatikan untuk font tanpa varian itu, karena dompdf diam-diam
  berpindah ke Helvetica jika varian yang diminta tidak terdaftar.
- **Gambar latar** disimpan di disk publik, diperkecil ke sisi terpanjang 3508 px, maksimal 5 MB. Gambar dipakai bersama
  antar versi turunan dan baru dihapus bila tidak ada versi lain yang memakainya.
- **Pratinjau PDF** di editor memakai mesin yang sama dengan sertifikat asli (endpoint `preview-draft`, throttle 20/menit,
  gambar sementara di disk privat dan dihapus setelah PDF dibuat).

### Positive Consequences
- Admin mengatur sertifikat sendiri; riwayat dan rollback tersedia.
- Tanpa dependensi atau build step baru; posisi konsisten di editor dan PDF.

### Negative Consequences / Trade-offs
- Satu template aktif global (belum per kursus atau kategori).
- Tampilan di browser dan PDF bisa berbeda sedikit; mitigasi: tombol Pratinjau PDF dan penyusutan teks yang meniru server.
- Mengaktifkan versi baru membuat PDF tiap siswa dibuat ulang saat diunduh berikutnya (sekali per siswa per kursus).
- Font hanya lima pilihan; menambah font berarti menambah berkas dan entri di `App\Support\CertificateFonts`.
