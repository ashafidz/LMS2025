# ADR-002: Badge Berbasis Aturan yang Disusun Admin

- **Status**: Accepted
- **Date**: 2026-10-25
- **Deciders**: Tim ruangstudi.id (permintaan fitur: badge baru tanpa mengubah kode)

---

## Context and Problem Statement
Badge sebelumnya hanya berupa gambar dan judul. Satu-satunya aturan pemberian ditulis di kode
(`BadgeService`: lulus 10 kuis memberi badge dengan ID 1), dan progres di halaman siswa ditebak dari kata
"Kuis" di judul. Setiap badge baru berarti pekerjaan developer, dan menghapus badge pertama membuat
pemberian badge salah sasaran. Superadmin dan admin (staf sekolah/kementerian) perlu membuat badge dan
mengatur syaratnya sendiri.

---

## Decision Drivers
- Pengguna awam: tanpa JSON, rumus, atau kode; cukup dropdown dan angka dalam kalimat.
- Hosting bersama: tanpa proses tambahan; antrean database dan cron yang sudah ada dipakai.
- Satu sumber kebenaran: angka progres di halaman siswa sama dengan angka yang dipakai saat memberi badge.
- Halaman siswa tidak boleh menjadi lebih lambat karena pemeriksaan badge.

---

## Considered Options
1. **Katalog ukuran tetap + syarat yang disusun admin** (dipilih): developer menulis ukuran satu kali
   (`BadgeMetrics`), admin menggabungkan ukuran, target, dan cakupan lewat form.
2. Mesin aturan bebas (admin menulis ekspresi/kondisi): fleksibel tetapi tidak cocok untuk pengguna awam
   dan sulit dites.
3. Event per jenis badge (listener tiap aksi): cepat untuk satu badge, tetapi tiap badge baru kembali ke
   developer.

---

## Decision
- Tabel `badge_rules` (badge_id, metric, target, scope, scope_id, position). Satu badge punya 1 sampai 3
  syarat, semuanya harus terpenuhi (AND). Operator selalu "minimal".
- `App\Support\Badges\BadgeMetrics` adalah katalog ukuran: label awam, satuan, cakupan yang didukung
  (semua kursus, kursus tertentu, kategori tertentu), dan cara menghitung. Menambah ukuran = satu entri baru.
- `BadgeService::progressFor` menghitung progres; `evaluate` dan `backfill` memberi badge memakai
  perhitungan yang sama.
- Pemicu: `BadgeService::queueFor($user)` dipanggil di titik yang mengubah progres siswa (poin,
  lulus kuis, nilai tugas, ulasan, sertifikat, komentar, pendaftaran kursus). Itu mengantre job
  `EvaluateUserBadges` (unik per siswa sampai mulai diproses, dispatch `afterCommit`). Jaring pengaman:
  `php artisan badges:award` dijadwalkan harian 02:00.
- Aturan produk:
  - Badge yang sudah didapat tidak pernah dicabut walau data berubah.
  - Syarat dikunci setelah ada siswa yang mendapat badge; ubah lewat Duplikat.
  - Kuis dihitung per kuis berbeda (mengulang kuis yang sama tidak menambah hitungan). Nilai sempurna
    memakai `COALESCE(revised_score, scaled_score)`, sama dengan nilai akhir yang dilihat siswa.
  - Badge lama ber-ID 1 dimigrasikan menjadi `quiz_passed >= 10`; pemilik lama tidak diberi notifikasi.
  - Ikon SVG ditolak (bisa memuat script dan disajikan dari disk publik).
- Notifikasi: kolom `badge_user.seen_at`; badge baru ditampilkan sekali (maksimal 3) lewat view composer
  layout, lalu ditandai sudah dilihat.

---

## Consequences
- Positif: admin membuat badge baru tanpa developer; progres siswa jujur (badge tanpa syarat tampil sebagai
  "Diberikan oleh admin", bukan 0/1 palsu); halaman siswa tidak menunggu pemeriksaan badge.
- Negatif: badge bisa tertunda sampai antrean berjalan (cron tiap menit). Ukuran baru tetap butuh developer.
- Belum didukung: badge beruntun (butuh pencatatan login harian), syarat OR, rentang waktu, badge per
  kursus buatan instruktur, hadiah poin saat mendapat badge.
