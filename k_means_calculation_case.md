# Penjelasan Perhitungan Matematis K-Means & Profiling LMS

Dokumen ini menjelaskan secara rinci bagaimana sistem *Learning Management System* (LMS) mengubah jawaban pilihan ganda dan skala Likert milik siswa menjadi matriks profil, dan bagaimana algoritma K-Means mengelompokkan mereka.

---

## 1. Fase Ekstraksi Skor (Skoring Jawaban)
Sebelum masuk ke algoritma AI, data jawaban mentah siswa harus diolah menjadi angka murni (fitur). Aplikasi LMS kita mendeteksi **10 Dimensi Fitur** untuk setiap siswa.

### A. Fitur Persentase Kontribusi (Goal Setting & SDT)
Bagian ini menggunakan soal berskala Likert 1-5. Sistem kita menghitung **persentase kontribusi** masing-masing dimensi di dalam komponennya.

**Contoh Kasus Siswa 1 (Akhmad Nabil):**
*   **Komponen Goal Setting** memiliki 2 dimensi: *Mastery Goal* (4 soal) dan *Performance Goal* (4 soal).
*   Misal total jawaban skala Likert untuk *Mastery* adalah **20** (semua dijawab 5), dan *Performance* adalah **16** (semua dijawab 4).
*   Total Poin Keseluruhan = 20 + 16 = 36.
*   **Perhitungan Fitur 1 (Mastery):** `(20 / 36) * 100% = 55.56%`
*   **Perhitungan Fitur 2 (Performance):** `(16 / 36) * 100% = 44.44%`

*(Aturan ini juga berlaku sama untuk menghitung persentase Autonomy, Competence, dan Relatedness).*

### B. Fitur Nilai Mutlak & Rata-rata
*   **Fitur Prior Knowledge:** Nilai mutlak ujian pilihan ganda. Misal dari 3 soal, benar 2. Maka skornya: `(2 / 3) * 100% = 66.67`.
*   **Fitur Kebutuhan AI:** Menggunakan murni nilai rata-rata dari jawaban Likert (tanpa persentase). Misal 3 soal Transparency dijawab (4, 5, 5). Maka skornya: `(4+5+5) / 3 = 4.67`.

---

## 2. Fase Pembentukan Matriks Fitur (Feature Matrix)
Setelah nilai terkumpul, sistem menyusun matriks vektor 10 Dimensi. Berikut adalah data asli yang ditangkap dari *database* untuk 3 siswa kita:

| ID | Mastery | Perform. | Prior Know. | Autonomy | Compet. | Related. | Transp. | Guid. | Adapt. | Feed. |
|:---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **S1** | 55.56 | 44.44 | **0.00** | 31.91 | 34.04 | 34.04 | 4.50 | 4.00 | 4.67 | 4.33 |
| **S2** | **43.75** | **56.25** | 66.67 | 33.33 | 34.92 | 31.75 | 4.50 | 4.00 | 5.00 | 4.33 |
| **S3** | 52.63 | 47.37 | 66.67 | 31.58 | 34.21 | 34.21 | 4.50 | 4.33 | 4.67 | 4.00 |

> [!WARNING]
> **Masalah Skala Tumpang Tindih:**
> Coba lihat data di atas. *Prior Knowledge* memiliki rentang (0 - 100). Sedangkan *Guidance* memiliki rentang kecil (1 - 5). Jika K-Means langsung menghitung jarak pada data mentah ini, fitur skala besar akan mendominasi perhitungan (bias).

---

## 3. Fase Standarisasi (Z-Scale Standardization)
Untuk menghilangkan bias, pustaka Rubix ML menerapkan Standarisasi Z-Scale. Formula matematiknya:

```math
Z = \frac{X - \mu}{\sigma}
```
*(Nilai dikurangi Rata-rata populasi, dibagi Standar Deviasi)*

Efeknya, semua 10 fitur tersebut kini memiliki skala yang sama rata, tidak peduli apakah awalnya bernilai ratusan atau cuma belasan.

---

## 4. Fase Pemilihan Jumlah Kluster (Metode Kneedle / Elbow)
Bagaimana sistem tahu harus membagi menjadi berapa kelompok? Sistem melatih K-Means mulai dari `K=2` hingga batas maksimal siswa, lalu menghitung tingkat kesalahan (***Inertia*** / *Sum of Squared Errors*).

Sistem mencari **"Titik Siku" (Elbow)** menggunakan algoritma Geometri Geodesik:
1. Menarik garis imajiner dari titik inersia `K-pertama` lurus menuju titik inersia `K-terakhir`.
2. Menghitung jarak tegak lurus tertinggi dari setiap titik *K* ke garis lurus tersebut menggunakan rumus jarak Titik ke Garis:
   `Distance = |(y2 - y1)*x0 - (x2 - x1)*y0 + x2*y1 - y2*x1| / sqrt((y2 - y1)^2 + (x2 - x1)^2)`
3. Titik yang memiliki jarak (*Distance*) paling jauh dari garis imajiner adalah **nilai K Paling Optimal**. 

*(Dalam kasus data kecil 3 siswa ini, karena batas pengelompokan yang sempit, sistem secara otomatis merosot (*fallback*) menetapkan **K=2** untuk menghindari error pembagian kelompok).*

---

## 5. Fase Clustering K-Means Ruang 10 Dimensi
Sekarang K-Means akan menyebar 2 titik *Centroid* acak, dan menghitung Jarak Euclidean (*Euclidean Distance*) antara 10 Dimensi tiap siswa dengan *Centroid* tersebut:

```math
Distance = \sqrt{(X_1 - C_1)^2 + (X_2 - C_2)^2 + ... + (X_{10} - C_{10})^2}
```

**Kenapa Siswa 1 & 3 masuk satu kluster (Kluster 1), sedangkan Siswa 2 diisolasi (Kluster 2)?**
Mari kita lihat data aslinya kembali. Meskipun Siswa 3 dan Siswa 2 memiliki *Prior Knowledge* yang kembar (66.67), namun pada sisa **9 Dimensi** lainnya, Siswa 3 lebih condong menyerupai Siswa 1.

*   S1 vs S3 -> Perbedaan pola Mastery/Performance hanya **~3%**.
*   S2 vs S3 -> Perbedaan pola Mastery/Performance mencapai **~9%** (S2 lebih mementingkan angka/Performance, S3 lebih ke penguasaan/Mastery).
*   S2 vs S3 -> Perbedaan Relatedness (S2 tidak suka belajar kelompok, S3 suka).

Karena K-Means mempertimbangkan **seluruh 10 dimensi** tanpa pilih kasih berkat Standarisasi, secara matematis jarak total Siswa 3 memang terbukti lebih dekat ke arah Centroid Siswa 1.

---

## 6. Fase Reduksi ke Visualisasi 2D (Sumbu X dan Y Grafik)
Ruang 10 Dimensi tentu tidak bisa digambar ke monitor yang cuma berbentuk layar 2 Dimensi. 

Untuk visualisasi dasbor *(Scatter Plot)*, kita mengekstrak fitur yang paling krusial dari matriks aslinya untuk menjadi kordinat grafik tanpa memanipulasi keanggotaan klusternya:
*   **Kordinat X:** `Mastery Goal %` -> Menggambarkan preferensi motivasi secara umum.
*   **Kordinat Y:** `Prior Knowledge %` -> Menggambarkan kesiapan kognitif.

**Hasil Visualisasi:**
*   S1 (Kluster 1) -> Tampil di koordinat `(55.56, 0.00)`
*   S3 (Kluster 1) -> Tampil di koordinat `(52.63, 66.67)`
*   S2 (Kluster 2) -> Tampil di koordinat `(43.75, 66.67)`

Meskipun di gambar layar (2D) S1 dan S3 kelihatan berjauhan di sumbu vertikal, mereka aslinya tergabung di kluster (warna) yang sama karena kedekatan mereka yang luar biasa di 8 dimensi tersembunyi yang tidak ikut ditampilkan oleh grafik 2D.
