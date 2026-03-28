# Fitur Pengacakan Soal (Question Shuffle) — Penjelasan untuk Sidang Tugas Akhir

> Dokumen ini dibuat khusus untuk keperluan presentasi dan tanya jawab sidang Tugas Akhir. Isinya merangkum fitur **Question Shuffle** yang diimplementasikan pada sistem LMS2025, dilengkapi penjelasan alasan teknis dan akademis di balik setiap keputusan desain.

---

## 1. Latar Belakang & Motivasi

Pada ujian online, seringkali beberapa siswa mengerjakan kuis di waktu yang berdekatan atau di ruangan yang sama. Jika urutan soal selalu sama untuk semua siswa, ada potensi:

- Siswa A yang sudah selesai memberitahu jawaban "soal nomor 3" kepada siswa B
- Siswa merekam atau menyebar urutan soal sehingga urutan cepat diingat oleh orang lain

**Masalah yang diselesaikan fitur ini:**

> _"Bagaimana sistem memastikan setiap siswa mendapatkan urutan soal yang berbeda-beda, sehingga contekan berbasis nomor urut soal tidak efektif?"_

**Solusi yang diimplementasikan:**
Setiap kali siswa memulai kuis (membuat attempt baru), server mengacak urutan semua soal menggunakan **algoritma Fisher-Yates** dan menyimpan urutan unik tersebut ke database. Siswa lain yang memulai kuis pada waktu berbeda akan mendapatkan urutan yang berbeda.

---

## 2. Gambaran Umum Sistem

Seluruh proses shuffle berjalan di **sisi server (Laravel)** — tidak ada kode pengacakan di browser siswa. Siswa hanya menerima soal yang sudah diurutkan oleh server tanpa mengetahui urutan "asli" kuis.

```
Server saat begin():          Server saat take():          Browser siswa:
─────────────────────         ─────────────────────        ─────────────────
Ambil soal dari DB            Baca urutan dari DB          Tampilkan soal
     ↓                              ↓                      sesuai urutan
Fisher-Yates Shuffle          ORDER BY shuffled_order      yang server kirim
     ↓                              ↓
Simpan ke DB                  Kirim ke view
(permanen per attempt)
```

**Yang penting untuk dipahami:** Urutan diacak **sekali saja** saat siswa memulai kuis, hasilnya disimpan ke database, dan tidak berubah lagi sampai kuis selesai — bahkan jika siswa refresh atau jaringan terputus.

---

## 3. Teknologi yang Digunakan

| Komponen           | Teknologi                           | Alasan Pemilihan                                                    |
| ------------------ | ----------------------------------- | ------------------------------------------------------------------- |
| Algoritma Shuffle  | **Fisher-Yates Shuffle**            | Tidak bias — setiap permutasi memiliki probabilitas yang sama       |
| Random Number      | `random_int()` PHP                  | Cryptographically Secure PRNG (CSPRNG) — tidak dapat diprediksi     |
| Penyimpanan Urutan | Tabel `quiz_attempt_question_order` | Urutan persisten per-attempt, konsisten saat refresh                |
| Backend            | **Laravel 11** (PHP 8.2)            | Framework utama LMS2025                                             |
| Service Layer      | `QuizShuffleService`                | Logika shuffle dipisahkan ke service khusus (Single Responsibility) |

---

## 4. Cara Kerja — Penjelasan Singkat

### Langkah 1: Instruktur Mengaktifkan Shuffle

Di halaman Pengaturan Keamanan Kuis, instruktur mencentang "Acak Urutan Soal". Preferensi ini disimpan ke kolom `enable_question_shuffle` di tabel `quiz_security_settings`.

### Langkah 2: Siswa Memulai Kuis

Saat siswa menekan tombol "Mulai Kuis":

1. Server membuat record `QuizAttempt` baru (status: `in_progress`)
2. `QuizShuffleService` mengambil semua `question_id` yang terhubung ke kuis
3. Fisher-Yates mengacak array ID soal tersebut
4. Setiap soal + posisi barunya disimpan ke tabel `quiz_attempt_question_order`

### Langkah 3: Siswa Mengerjakan Soal

Setiap kali halaman soal dimuat (termasuk saat refresh):

- Server membaca tabel `quiz_attempt_question_order` untuk attempt ini
- Soal diambil dengan `ORDER BY shuffled_order` → urutan selalu konsisten
- View menerima soal yang sudah berurutan, tidak tahu tentang shuffle

### Langkah 4: Penilaian Jawaban

Saat siswa submit:

- Jawaban dikirim sebagai `answers[question_id]` = `option_id`
- Server menilai berdasarkan `question_id`, **bukan nomor urut**
- Shuffle sama sekali tidak mempengaruhi akurasi penilaian

---

## 5. Algoritma Fisher-Yates — Penjelasan Sederhana

Fisher-Yates adalah cara mengacak array yang **adil dan efisien**. Cara kerjanya:

> Bayangkan 5 soal dimasukkan ke dalam kantong. Kamu mengambil satu soal acak dari kantong, meletakkannya di posisi terakhir yang belum terisi, lalu mengambil satu lagi dari soal yang tersisa — terus sampai kantong kosong.

**Secara matematis:**

- Mulai dari elemen terakhir (posisi ke-n)
- Pilih posisi acak antara 0 dan posisi saat ini
- Tukar elemen di posisi saat ini dengan elemen di posisi acak tersebut
- Mundur satu posisi, ulangi

**Contoh dengan 5 soal [10, 20, 30, 40, 50]:**

| Langkah         | Tukar posisi  | Hasil                          |
| --------------- | ------------- | ------------------------------ |
| i=4, j=1 (acak) | pos-4 ↔ pos-1 | `[10, **50**, 30, 40, **20**]` |
| i=3, j=3 (acak) | pos-3 ↔ pos-3 | `[10, 50, 30, 40, 20]` (sama)  |
| i=2, j=0 (acak) | pos-2 ↔ pos-0 | `[**30**, 50, **10**, 40, 20]` |
| i=1, j=1 (acak) | pos-1 ↔ pos-1 | `[30, 50, 10, 40, 20]` (sama)  |

**Hasil akhir yang tersimpan ke DB:**

| Nomor Tampil ke Siswa | ID Soal Asli |
| :-------------------: | :----------: |
|        Soal 1         |    ID: 30    |
|        Soal 2         |    ID: 50    |
|        Soal 3         |    ID: 10    |
|        Soal 4         |    ID: 40    |
|        Soal 5         |    ID: 20    |

---

## 6. Kenapa Menggunakan `random_int()`, Bukan `rand()`?

Ini adalah keputusan keamanan yang perlu dijelaskan:

| Fungsi         | Tipe                                  | Sifat                                                       |
| -------------- | ------------------------------------- | ----------------------------------------------------------- |
| `rand()`       | PRNG (Pseudo-Random)                  | Deterministis — jika seed diketahui, urutan bisa diprediksi |
| `random_int()` | **CSPRNG** (Cryptographically Secure) | Menggunakan entropi OS — tidak dapat diprediksi             |

Dalam konteks ujian online, jika seseorang bisa memprediksi urutan shuffle maka mereka bisa mempersiapkan contekan yang terorganisir. `random_int()` menutup celah ini.

---

## 7. Kenapa Urutan Disimpan ke Database?

Solusi alternatif yang lebih sederhana adalah mengacak di memori setiap kali halaman dimuat:

```php
// Alternatif TIDAK dipilih:
$questions = $quiz->questions->shuffle();
```

Pendekatan ini ditolak karena masalah **konsistensi**:

- Setiap refresh → urutan berbeda → siswa bingung, jawaban yang sudah diisi "berpindah soal"
- Crash browser → saat kembali, soal pertama bisa berbeda → siswa tidak tahu sudah menjawab yang mana
- Tidak ada jejak audit — tidak bisa dibuktikan soal apa yang diterima siswa jika ada sengketa

Dengan menyimpan ke database, urutan soal menjadi bagian dari **bukti auditable** sesi ujian siswa.

---

## 8. Apakah Shuffle Mempengaruhi Penilaian?

**Tidak.** Ini sering menjadi pertanyaan utama.

Kuncinya ada di cara form HTML mengirim jawaban:

```html
<!-- Input nama menggunakan question_id, bukan nomor urut -->
<input type="radio" name="answers[30]" value="option_id" />
<input type="radio" name="answers[50]" value="option_id" />
```

Ketika siswa submit, server menerima: `answers = { '30': pilihan, '50': pilihan, ... }`

Server lalu melakukan iterasi soal berdasarkan `question_id`:

```php
foreach ($quizQuestions as $question) {
    $jawaban = $userAnswers[$question->id]; // ← key adalah ID, bukan nomor urut
    $benar   = $this->checkAnswer($question, $jawaban);
    // ...
}
```

Soal nomor 30 dinilai berdasarkan kunci jawaban soal 30, terlepas dari apakah ia muncul di posisi ke-1, ke-3, atau ke-5 saat dikerjakan siswa.

---

## 9. Konfigurasi oleh Instruktur

| Pengaturan       | Default     | Fungsi                                 |
| ---------------- | ----------- | -------------------------------------- |
| Acak Urutan Soal | ❌ Nonaktif | Master switch pengacakan soal per-kuis |

Model **opt-in** dipilih (default OFF) karena shuffle bersifat mengubah pengalaman pengerjaan soal secara fundamental. Instruktur mengaktifkannya secara sadar sesuai kebutuhan — misalnya, untuk kuis yang memiliki banyak peserta serentak.

---

## 10. Keterbatasan Sistem

Transparansi akademis mengharuskan mengakui keterbatasan implementasi:

1. **Hanya acak urutan soal — belum acak opsi jawaban** — Jika soal pilihan ganda, opsi A/B/C/D tetap di posisi yang sama di setiap siswa. Siswa yang berbagi "Soal X, jawabannya B" masih efektif karena opsi tidak diacak.

2. **Satu shuffle per attempt** — Jika siswa mencoba kuis lagi (attempt baru), ia mendapat urutan acak yang baru. Tapi jika kuis hanya satu kali attempt, semua siswa mendapat shuffle berbeda-beda, namun siswa tidak dapat mencoba ulang untuk mendapat urutan yang "lebih mudah".

3. **Mode Preview tidak melakukan shuffle** — Ketika instruktur menggunakan fitur preview kuis, soal selalu ditampilkan dalam urutan asli. Ini memudahkan instruktur mereview soal, tapi berarti tampilan preview tidak identik dengan pengalaman siswa sesungguhnya.

4. **Bank soal tidak dibagi per kategori** — Shuffle dilakukan pada semua soal sekaligus. Jika instruktur ingin soal dikelompokkan (misalnya: soal mudah di awal, soal susah di akhir), fitur ini belum mendukungnya.

---

## 11. Poin-Poin Kemungkinan Pertanyaan Penguji

**Q: Mengapa memilih Fisher-Yates dan bukan algoritma lain?**

> A: Fisher-Yates adalah algoritma standar industri untuk pengacakan array yang tidak bias, dengan kompleksitas waktu O(n) dan ruang O(1). Alternatif seperti sort dengan random comparator memiliki distribusi yang tidak seragam — beberapa permutasi lebih sering muncul dari yang lain, sehingga tidak benar-benar "adil". Fisher-Yates menjamin setiap permutasi memiliki probabilitas `1/n!` yang sama persis.

**Q: Bagaimana dengan keamanan — apakah urutan bisa diprediksi?**

> A: Sistem menggunakan `random_int()` yang merupakan CSPRNG (Cryptographically Secure Pseudo-Random Number Generator) berbasis entropi OS, bukan `rand()` yang menggunakan seed deterministis. Urutan tidak dapat diprediksi tanpa akses langsung ke database.

**Q: Bagaimana jika dua siswa mendapat urutan yang sama?**

> A: Ini bisa terjadi secara kebetulan — ada `n!` kemungkinan permutasi dari `n` soal, dan dengan banyak siswa, kemungkinan tabrakan memang ada (Birthday Paradox). Namun probabilitasnya sangat kecil: untuk 10 soal saja ada 3.628.800 kemungkinan urutan. Sistem tidak menjamin setiap siswa mendapat urutan unik, hanya menjamin setiap siswa mendapat urutan yang diacak secara independen.

**Q: Apakah shuffle bisa menyebabkan soal dinilai salah?**

> A: Tidak, karena sistem menggunakan `question_id` sebagai kunci penilaian, bukan nomor urut tampilan. Soal ke-1 yang tampil di layar siswa bisa saja soal dengan ID=30, dan jawaban yang dikirim pun adalah `answers[30]` — server menilai dengan kunci jawaban soal ID=30 yang benar.

**Q: Mengapa perlu tabel terpisah `quiz_attempt_question_order`?**

> A: Agar urutan soal bersifat persisten dan auditable. Jika shuffle dilakukan di memori setiap kali halaman dimuat, urutan akan berbeda setiap refresh sehingga konsistensi pengerjaan terganggu. Dengan tabel terpisah, urutan menjadi bagian permanen dari rekam jejak attempt siswa — bisa diverifikasi ulang jika ada sengketa nilai atau klaim kecurangan.

---

## 12. Alur Teknis Ringkas (untuk Referensi Cepat)

```
[Instruktur: aktifkan "Acak Urutan Soal" di Pengaturan Keamanan]
    ↓ quiz_security_settings.enable_question_shuffle = true

[Siswa klik "Mulai Kuis"]
    ↓
[begin(): buat QuizAttempt (status: in_progress)]
    ↓
[QuizShuffleService::generateShuffledOrder()]
    ├── Ambil question_ids dari quiz_question WHERE quiz_id = ?
    ├── Fisher-Yates Shuffle → urutan baru
    ├── DB::beginTransaction()
    ├── Simpan satu per satu ke quiz_attempt_question_order
    └── DB::commit()
    ↓
[Redirect ke take(attempt_id)]
    ↓
[take(): baca urutan dari DB → ORDER BY shuffled_order]
    ↓
[View: render soal dalam urutan acak]
    (Setiap input: name="answers[question_id]")
    ↓         ↓
[Refresh?]  [Submit?]
    ↓              ↓
[Baca DB lagi] [Nilai berdasarkan question_id]
[Urutan sama]  [Shuffle tidak mempengaruhi penilaian]
```
