# Dokumentasi Monitoring & Peninjauan Pelanggaran - LMS2025

Dokumen ini menjelaskan mekanisme teknis bagi instruktur untuk memantau integritas kuis, meninjau bukti pelanggaran (AI & Tab), dan melakukan revisi skor.

---

## 1. Dasbor Ringkasan Monitoring (Overview)
Sistem merangkum statistik pelanggaran dari seluruh kuis dalam satu kursus untuk memudahkan instruktur melakukan deteksi dini.

```php
// app/Http/Controllers/Instructor/InstructorQuizController.php

public function courseMonitoringOverview(Course $course)
{
    $quizzes = Quiz::whereHas('lesson.module', function ($query) use ($course) {
        $query->where('course_id', $course->id);
    })->with(['attempts.integritySummary'])->get();

    // Hitung total akumulasi pelanggaran
    $totalTabViolations = $quizzes->sum(fn($q) => $q->attempts->sum(fn($a) => $a->integritySummary?->total_tab_switches ?? 0));
    $totalCameraViolations = $quizzes->sum(fn($q) => $q->attempts->sum(fn($a) => $a->integritySummary?->total_face_violations ?? 0));

    return view('instructor.quizzes.course-monitoring-overview', compact('course', 'stats'));
}
```

---

## 2. Log Pelanggaran Detail (Evidence Review)
Setiap sesi pengerjaan memiliki timeline pelanggaran yang mencatat waktu, jenis, dan bukti visual (screenshot).

### Struktur Data Log (MonitoringLog)
| Kolom | Deskripsi |
|---|---|
| `violation_type` | Jenis (tab_switch, look_left, face_not_detected, dll) |
| `violation_timestamp` | Waktu tepat kejadian berlangsung |
| `screenshot_path` | Lokasi file bukti foto di storage |
| `duration_seconds` | Berapa lama pelanggaran tersebut dilakukan |

### Logika Tampilan Timeline di View
```html
@foreach($attempt->monitoringLogs as $log)
<tr>
    <td>{{ $log->violation_timestamp->format('H:i:s') }}</td>
    <td><span class="badge">{{ $log->violation_type }}</span></td>
    <td>
        @if($log->screenshot_path)
            <img src="{{ asset('storage/' . $log->screenshot_path) }}" width="150">
        @endif
    </td>
</tr>
@endforeach
```

---

## 3. Mekanisme Revisi Skor (Audit Trail)
Instruktur dapat mengubah nilai akhir jika bukti pelanggaran dianggap tidak valid (misal: kesalahan teknis kamera). Skor asli tetap disimpan untuk transparansi.

```php
// app/Http/Controllers/Instructor/InstructorQuizController.php

public function reviseScore(Request $request, QuizAttempt $attempt)
{
    $request->validate([
        'revised_score' => 'required|numeric|min:0',
        'revision_note' => 'required|string|max:500',
    ]);

    $attempt->update([
        'revised_score' => $request->revised_score,
        'revised_by' => Auth::id(),
        'revised_at' => now(),
        'revision_note' => $request->revision_note,
    ]);

    return redirect()->back()->with('success', 'Skor berhasil direvisi.');
}
```

---

## 4. Struktur Database Pendukung
- **`quiz_attempt_integrity_summaries`**: Menyimpan total angka pelanggaran per sesi agar load data lebih cepat (tidak perlu menghitung ulang log setiap saat).
- **`monitoring_logs`**: Menyimpan detail setiap kejadian (event) termasuk path ke file gambar di storage.

---

## 5. Fase Kalibrasi Kamera (Grace Period)

Saat kamera pertama kali diaktifkan, model AI (MediaPipe Face Mesh) membutuhkan waktu beberapa detik untuk mengunduh WASM binary dan model weights, lalu melakukan warm-up inference. Selama periode ini, hasil deteksi belum stabil dan dapat menghasilkan **false-positive** (pelanggaran palsu).

### Mekanisme Pencegahan
Sistem menerapkan **grace period selama 10 detik** setelah kamera berhasil diinisialisasi. Warm-up baru dianggap selesai jika **KEDUA syarat** terpenuhi:

| Syarat | Deskripsi |
|---|---|
| **Grace period timer habis** | Minimal 10 detik telah berlalu sejak kamera aktif (minimum wait time) |
| **Warm-up frames cukup** | Minimal 5 frame deteksi wajah berhasil diterima dari model AI (bukti model sudah stabil) |

Selama warm-up berlangsung:

| Aspek | Perilaku |
|---|---|
| **Deteksi pelanggaran** | Semua hasil deteksi **diabaikan** — tidak ada violation yang dicatat ke server |
| **Visual feedback** | Overlay semi-transparan ditampilkan di atas preview kamera dengan teks "Kalibrasi Kamera" dan countdown timer |
| **Badge status** | Berubah dari `Memuat...` → `⏳ Kalibrasi...` → `🟢 Aktif` |
| **Fallback text** | Jika timer habis tapi frame belum cukup, overlay menampilkan "Mendeteksi wajah..." |

### Flow Teknis
```
camera.start()
  → isWarmingUp = true
  → Overlay "Kalibrasi Kamera" + countdown (10s) ditampilkan
  → onFaceMeshResults() dipanggil tiap frame:
      → Jika wajah terdeteksi: warmupSuccessFrames++
      → Jika frames >= 5: panggil tryEndWarmupPhase()
  → setTimeout(10s):
      → gracePeriodTimerExpired = true
      → panggil tryEndWarmupPhase()

tryEndWarmupPhase():
  → Cek: timer expired? DAN frames cukup?
  → Jika BELUM keduanya: return (tunggu)
  → Jika SUDAH keduanya:
      → isWarmingUp = false
      → Reset tracking state
      → Overlay fade-out → Badge "Aktif"
      → Deteksi pelanggaran DIMULAI
```

### Konfigurasi
| Konstanta | Default | Deskripsi |
|---|---|---|
| `GRACE_PERIOD_MS` | `10000` (10 detik) | Durasi grace period setelah kamera aktif |
| `WARMUP_FRAMES_REQUIRED` | `5` | Minimal frame deteksi wajah berhasil selama warm-up (untuk logging) |

---

## 6. Ringkasan Tipe Pelanggaran
1.  **Tab Switch**: Terdeteksi melalui JavaScript Page Visibility API.
2.  **Face Not Detected**: Siswa tidak berada di depan kamera.
3.  **Look Left/Right/Up/Down**: Gerakan kepala yang mencurigakan dideteksi oleh MediaPipe AI.
4.  **Expelled**: Status otomatis jika siswa melebihi ambang batas (threshold) yang ditentukan instruktur.
