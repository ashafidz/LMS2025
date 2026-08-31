# Panduan Branching Strategy (GitFlow) & CI/CD

Proyek ini menggunakan **standar GitFlow** (tanpa mewajibkan ekstensi CLI `git-flow`) yang dipadukan dengan **GitHub Flow** (Pull Requests) dan pengecekan CI otomatis.

## 🌳 Struktur Branch

Proyek ini memiliki struktur branch sebagai berikut:

- **`main`**: Branch utama yang selalu berisi kode stabil dan siap rilis ke *Production* (cPanel). Setiap perubahan di sini akan memicu deployment otomatis.
- **`develop`**: Branch integrasi utama. Semua fitur baru, perbaikan bug (selain hotfix), dan eksperimen yang sudah stabil akan digabungkan ke sini terlebih dahulu.
- **`development-ai`**: *Long-lived epic branch*. Ini adalah branch khusus yang berumur panjang untuk pengembangan fitur AI. Branch ini memiliki siklus deployment sendiri ke server VPS Docker secara otomatis. Pada saat yang tepat, branch ini akan digabungkan ke `develop`.
- **`feature/*`**: Branch berumur pendek untuk mengembangkan satu fitur spesifik. Dibuat dari `develop` dan akan di-merge kembali ke `develop`.
- **`hotfix/*`**: Branch darurat untuk memperbaiki *bug* kritis di Production. Dibuat dari `main` dan di-merge kembali ke `main` serta `develop`.

---

## 🛠️ Alur Kerja Tim (Workflow)

Untuk memastikan kode yang masuk selalu teruji dan bersih, tim diwajibkan mengikuti alur kerja berikut:

### 1. Mengerjakan Fitur Biasa (Non-AI)
1. **Perbarui lokal:** `git checkout develop && git pull origin develop`
2. **Buat branch fitur:** `git checkout -b feature/nama-fitur-kamu`
3. Kerjakan kode, lalu commit.
4. **Push ke remote:** `git push -u origin feature/nama-fitur-kamu`
5. Buka GitHub dan buat **Pull Request (PR)** dari branch fiturmu menuju `develop`.
6. Biarkan **Automated CI (Testing & Linting)** berjalan. Jika merah (gagal), perbaiki kodenya. Jika hijau (sukses), minta persetujuan rekan tim untuk melakukan *Merge*.

### 2. Mengerjakan Fitur AI
Karena `development-ai` adalah branch eksperimental yang langsung terhubung ke server pengujian AI:
1. **Perbarui lokal:** `git checkout development-ai && git pull origin development-ai`
2. **(Opsional) Buat sub-branch:** `git checkout -b feature-ai/nama-model` jika ingin lebih terstruktur.
3. Jika langsung melakukan push ke `development-ai`, kode akan **otomatis di-deploy ke server VPS**.
4. Fitur-fitur ini pada akhirnya akan di-merge ke `develop` menggunakan Pull Request ketika sudah stabil untuk aplikasi utama.

### 3. Mengatasi Bug Darurat (Hotfix)
Jika ada laporan error kritis di server Production:
1. **Buat branch dari main:** `git checkout main && git checkout -b hotfix/nama-error`
2. Perbaiki bug, lalu commit dan push ke origin.
3. Buat **Pull Request (PR)** menuju `main`.
4. Setelah di-merge ke `main`, pastikan **PENTING** untuk meng-update `develop` juga agar bug tidak kembali lagi:
   `git checkout develop && git pull origin develop && git merge main && git push origin develop`

---

## 🤖 Continuous Integration (CI)
Setiap kali ada Pull Request yang ditujukan ke `main`, `develop`, atau `development-ai`, sistem CI GitHub Actions (`ci.yml`) akan:
1. Menjalankan `vendor/bin/pint --test` untuk memastikan standarisasi *Code Style*.
2. Menjalankan `php artisan test` untuk memastikan *Unit* dan *Feature Tests* tidak ada yang rusak.
Kode tidak boleh di-merge jika CI gagal.
