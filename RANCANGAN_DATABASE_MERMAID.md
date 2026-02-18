# Rancangan Relasi Database - Fitur Keamanan Kuis Online

## Platform E-Learning EduGames

## Diagram Relasi Antar Tabel (ERD)

```mermaid
erDiagram
    users ||--o{ quiz_attempts : "mengerjakan"
    quizzes ||--o{ quiz_attempts : "memiliki"
    quizzes ||--|| quiz_security_settings : "memiliki"
    quiz_attempts ||--|| quiz_attempt_integrity_summary : "memiliki"
    quiz_attempts ||--o{ quiz_attempt_question_order : "memiliki"
    quiz_attempts ||--o{ monitoring_logs : "memiliki"
    quiz_attempts ||--o{ camera_access_logs : "memiliki"
    quiz_attempts ||--o{ student_answers : "memiliki"
    questions ||--o{ quiz_attempt_question_order : "muncul_di"
    questions ||--o{ student_answers : "dijawab"
    question_options ||--o{ student_answers : "dipilih"

    users {
        bigint id PK
        string name
        string email
        string role
    }

    quizzes {
        bigint id PK
        string title
        text description
        integer pass_mark
        integer time_limit
        boolean allow_exceed_time_limit
        boolean reveal_answers
        integer max_attempts
        datetime available_from
        datetime available_to
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    quiz_security_settings {
        bigint id PK
        bigint quiz_id FK "UNIQUE"
        boolean enable_camera_detection
        boolean enable_tab_detection
        boolean enable_question_shuffle
        integer camera_violation_threshold
        integer tab_violation_threshold
        integer face_detection_interval_seconds
        timestamp created_at
        timestamp updated_at
    }

    quiz_attempts {
        bigint id PK
        bigint quiz_id FK
        bigint student_id FK
        decimal score
        enum status "passed|failed|in_progress"
        timestamp start_time
        timestamp end_time
        timestamp created_at
        timestamp updated_at
    }

    quiz_attempt_question_order {
        bigint id PK
        bigint attempt_id FK
        bigint question_id FK
        integer shuffled_order
        timestamp created_at
    }

    monitoring_logs {
        bigint id PK
        bigint attempt_id FK
        enum violation_type "tab_switch|face_not_detected|look_left|look_right|look_down|look_up"
        timestamp violation_timestamp
        integer duration_seconds
        string screenshot_path
        json additional_data
        timestamp created_at
    }

    quiz_attempt_integrity_summary {
        bigint id PK
        bigint attempt_id FK "UNIQUE"
        integer total_tab_switches
        integer total_face_violations
        integer face_not_detected_count
        integer look_left_count
        integer look_right_count
        integer look_down_count
        integer look_up_count
        decimal integrity_score
        enum risk_level "low|medium|high"
        boolean flagged_for_review
        timestamp created_at
        timestamp updated_at
    }

    camera_access_logs {
        bigint id PK
        bigint attempt_id FK
        timestamp permission_requested_at
        boolean permission_granted
        timestamp permission_granted_at
        text browser_info
        text error_message
        timestamp created_at
    }

    questions {
        bigint id PK
        bigint topic_id FK
        text question_text
        enum question_type
        integer score
        timestamp created_at
        timestamp updated_at
    }

    question_options {
        bigint id PK
        bigint question_id FK
        text option_text
        boolean is_correct
        string correct_gap_identifier
        timestamp created_at
    }

    student_answers {
        bigint id PK
        bigint attempt_id FK
        bigint question_id FK
        bigint selected_option_id FK
        boolean is_correct
        timestamp created_at
        timestamp updated_at
    }
```

## Diagram Alur Data - Student Mengerjakan Kuis

```mermaid
flowchart TD
    Start([Student Klik Mulai Kuis]) --> CheckSecurity{Cek Security Settings}

    CheckSecurity --> GetSettings[SELECT quiz_security_settings<br/>WHERE quiz_id = ?]
    GetSettings --> CameraEnabled{enable_camera_detection<br/>= TRUE?}

    CameraEnabled -->|Yes| RequestCamera[Request Camera Permission]
    RequestCamera --> LogCamera[INSERT camera_access_logs]
    LogCamera --> CameraGranted{Permission<br/>Granted?}

    CameraGranted -->|No| ShowError[Tampilkan Error & Stop]
    CameraGranted -->|Yes| CreateAttempt
    CameraEnabled -->|No| CreateAttempt

    CreateAttempt[INSERT quiz_attempts<br/>status = in_progress] --> ShuffleEnabled{enable_question_shuffle<br/>= TRUE?}

    ShuffleEnabled -->|Yes| FisherYates[Jalankan Fisher-Yates Shuffle]
    FisherYates --> SaveOrder[INSERT quiz_attempt_question_order<br/>untuk setiap soal dengan<br/>shuffled_order]
    SaveOrder --> DisplayQuiz

    ShuffleEnabled -->|No| DisplayQuiz[Tampilkan Soal ke Student]

    DisplayQuiz --> MonitoringStart[Start Monitoring]

    MonitoringStart --> TabDetection[Tab Detection<br/>JavaScript visibilitychange]
    MonitoringStart --> FaceDetection[Face Detection<br/>MediaPipe setiap X detik]

    TabDetection -->|Tab Switch| LogTab[INSERT monitoring_logs<br/>violation_type = tab_switch]

    FaceDetection -->|Face Not Detected| LogFaceNot[INSERT monitoring_logs<br/>violation_type = face_not_detected]
    FaceDetection -->|Look Left/Right/Up/Down| Capture[Capture Screenshot]
    Capture --> LogFace[INSERT monitoring_logs<br/>dengan screenshot_path]

    LogTab --> CheckThreshold{Total Violations<br/>> Threshold?}
    LogFace --> CheckThreshold
    LogFaceNot --> CheckThreshold

    CheckThreshold -->|Yes| ShowWarning[Tampilkan Warning ke Student]
    CheckThreshold -->|No| ContinueQuiz
    ShowWarning --> ContinueQuiz

    ContinueQuiz[Student Menjawab Soal] --> SaveAnswer[INSERT/UPDATE student_answers]
    SaveAnswer --> MoreQuestions{Ada Soal<br/>Lagi?}

    MoreQuestions -->|Yes| DisplayQuiz
    MoreQuestions -->|No| FinishQuiz[Student Klik Selesaikan/Timeout]

    FinishQuiz --> CalculateScore[Hitung Skor Akhir]
    CalculateScore --> CalculateIntegrity[Hitung Integrity Summary]

    CalculateIntegrity --> CountViolations[COUNT violations<br/>per type dari monitoring_logs]
    CountViolations --> CalcScore[integrity_score = 100 -<br/>total_violations * penalty]
    CalcScore --> DetermineRisk{Tentukan<br/>Risk Level}

    DetermineRisk -->|score >= 80| SetLow[risk_level = low]
    DetermineRisk -->|50 <= score < 80| SetMedium[risk_level = medium]
    DetermineRisk -->|score < 50| SetHigh[risk_level = high]

    SetLow --> CheckFlag
    SetMedium --> CheckFlag
    SetHigh --> CheckFlag

    CheckFlag{Violations ><br/>Threshold?} -->|Yes| FlagReview[flagged_for_review = TRUE]
    CheckFlag -->|No| NoFlag[flagged_for_review = FALSE]

    FlagReview --> UpdateAttempt
    NoFlag --> UpdateAttempt

    UpdateAttempt[UPDATE quiz_attempts<br/>SET score, status, end_time] --> InsertSummary[INSERT<br/>quiz_attempt_integrity_summary]

    InsertSummary --> ShowResult[Tampilkan Halaman Hasil<br/>Skor & Status Kelulusan]
    ShowResult --> End([Selesai])
```

## Diagram Alur - Instructor Melihat Laporan Integritas

```mermaid
flowchart TD
    Start([Instructor Login]) --> ChooseReport{Pilih Jenis<br/>Laporan}

    ChooseReport -->|Per Attempt| ViewAttempt[Buka Detail Attempt]
    ChooseReport -->|Per Quiz| ViewQuiz[Buka Rekapitulasi Quiz]
    ChooseReport -->|Per Course| ViewCourse[Buka Rekapitulasi Course]

    ViewAttempt --> QueryAttempt[SELECT quiz_attempts,<br/>quiz_attempt_integrity_summary<br/>WHERE attempt_id = ?]
    QueryAttempt --> QueryLogs[SELECT monitoring_logs<br/>WHERE attempt_id = ?<br/>ORDER BY violation_timestamp]
    QueryLogs --> HasData1{Data<br/>Ada?}

    HasData1 -->|No| ShowEmpty1[Tampilkan Belum Ada Data]
    HasData1 -->|Yes| DisplayDetail[Tampilkan Detail Laporan:<br/>- Screenshot<br/>- Timeline Violations<br/>- Integrity Score<br/>- Risk Level]

    DisplayDetail --> CanRevise{Flagged for<br/>Review?}
    CanRevise -->|Yes| ShowRevise[Tampilkan Tombol Revisi Skor]
    ShowRevise --> ClickRevise{Instructor Klik<br/>Revisi?}
    ClickRevise -->|Yes| FormRevise[Tampilkan Form Revisi]
    FormRevise --> InputScore[Input Nilai Baru & Alasan]
    InputScore --> UpdateScore[UPDATE quiz_attempts<br/>SET score = ?]
    UpdateScore --> ShowSuccess[Tampilkan Pesan Sukses]

    CanRevise -->|No| End1
    ClickRevise -->|No| End1
    ShowSuccess --> End1([Selesai])
    ShowEmpty1 --> End1

    ViewQuiz --> QueryQuizAttempts[SELECT quiz_attempts,<br/>quiz_attempt_integrity_summary<br/>WHERE quiz_id = ?]
    QueryQuizAttempts --> AggregateQuiz[Agregasi Data:<br/>- Total Attempts<br/>- Avg Integrity Score<br/>- Risk Distribution<br/>- Flagged Count]
    AggregateQuiz --> HasData2{Data<br/>Ada?}

    HasData2 -->|No| ShowEmpty2[Tampilkan Belum Ada Partisipan]
    HasData2 -->|Yes| DisplayQuizReport[Tampilkan Dashboard Quiz:<br/>- Statistik Agregat<br/>- Chart Violations<br/>- Tabel Per Student]

    DisplayQuizReport --> End2([Selesai])
    ShowEmpty2 --> End2

    ViewCourse --> QueryCourseQuizzes[SELECT quizzes FROM course<br/>via modules & lessons]
    QueryCourseQuizzes --> QueryAllAttempts[SELECT semua quiz_attempts<br/>dan integrity_summary<br/>untuk semua quiz]
    QueryAllAttempts --> AggregateCourse[Agregasi Data Course:<br/>- Profil Risiko Siswa<br/>- Trend Violations<br/>- High Risk Students]
    AggregateCourse --> HasData3{Data<br/>Ada?}

    HasData3 -->|No| ShowEmpty3[Tampilkan Belum Ada Aktivitas]
    HasData3 -->|Yes| DisplayCourseReport[Tampilkan Dashboard Course:<br/>- Trend Chart<br/>- Risk Profile<br/>- Export Report]

    DisplayCourseReport --> End3([Selesai])
    ShowEmpty3 --> End3
```

## Diagram Alur - Setup Opsi Keamanan Kuis

```mermaid
flowchart TD
    Start([Instructor Buka Form Quiz]) --> FormType{Form Type}

    FormType -->|Add New| ShowForm[Tampilkan Form Kosong]
    FormType -->|Edit Existing| LoadQuiz[SELECT quiz WHERE id = ?]
    LoadQuiz --> LoadSecurity[SELECT quiz_security_settings<br/>WHERE quiz_id = ?]
    LoadSecurity --> PopulateForm[Isi Form dengan Data Existing]

    ShowForm --> DisplayOptions
    PopulateForm --> DisplayOptions[Tampilkan Security Options:<br/>- Enable Camera Detection<br/>- Enable Tab Detection<br/>- Enable Question Shuffle]

    DisplayOptions --> InstructorToggle[Instructor Toggle Checkbox]
    InstructorToggle --> SetThresholds[Instructor Set Thresholds<br/>Optional]
    SetThresholds --> ClickSave[Instructor Klik Simpan]

    ClickSave --> ValidateInput{Validasi<br/>Input}
    ValidateInput -->|Invalid| ShowError[Tampilkan Error Message]
    ShowError --> DisplayOptions

    ValidateInput -->|Valid| CheckMode{Mode}
    CheckMode -->|Add New| SaveQuiz[INSERT quizzes]
    SaveQuiz --> InsertSecurity[INSERT quiz_security_settings]

    CheckMode -->|Edit| UpdateQuiz[UPDATE quizzes]
    UpdateQuiz --> SecurityExists{Security Setting<br/>Exists?}

    SecurityExists -->|Yes| UpdateSecurity[UPDATE quiz_security_settings<br/>WHERE quiz_id = ?]
    SecurityExists -->|No| InsertSecurity

    InsertSecurity --> Success[Tampilkan Pesan Sukses]
    UpdateSecurity --> Success

    Success --> End([Selesai])
```

## Tabel-Tabel Baru yang Dikembangkan

### 1. quiz_security_settings

**Tujuan:** Menyimpan konfigurasi keamanan per kuis

| Kolom                           | Tipe             | Deskripsi                          |
| ------------------------------- | ---------------- | ---------------------------------- |
| id                              | bigint PK        | Primary key                        |
| quiz_id                         | bigint FK UNIQUE | Foreign key ke quizzes             |
| enable_camera_detection         | boolean          | Aktifkan deteksi kamera            |
| enable_tab_detection            | boolean          | Aktifkan deteksi perpindahan tab   |
| enable_question_shuffle         | boolean          | Aktifkan pengacakan soal           |
| camera_violation_threshold      | integer          | Batas toleransi pelanggaran kamera |
| tab_violation_threshold         | integer          | Batas toleransi perpindahan tab    |
| face_detection_interval_seconds | integer          | Interval deteksi wajah (detik)     |

### 2. quiz_attempt_question_order

**Tujuan:** Menyimpan urutan soal yang telah diacak (Fisher-Yates)

| Kolom          | Tipe      | Deskripsi                    |
| -------------- | --------- | ---------------------------- |
| id             | bigint PK | Primary key                  |
| attempt_id     | bigint FK | Foreign key ke quiz_attempts |
| question_id    | bigint FK | Foreign key ke questions     |
| shuffled_order | integer   | Urutan soal setelah diacak   |

### 3. monitoring_logs

**Tujuan:** Mencatat semua pelanggaran selama quiz

| Kolom               | Tipe      | Deskripsi                      |
| ------------------- | --------- | ------------------------------ |
| id                  | bigint PK | Primary key                    |
| attempt_id          | bigint FK | Foreign key ke quiz_attempts   |
| violation_type      | enum      | Jenis pelanggaran              |
| violation_timestamp | timestamp | Waktu pelanggaran              |
| duration_seconds    | integer   | Durasi pelanggaran             |
| screenshot_path     | string    | Path foto bukti                |
| additional_data     | json      | Data tambahan (koordinat, dll) |

### 4. quiz_attempt_integrity_summary

**Tujuan:** Ringkasan integritas per attempt (untuk performa)

| Kolom                   | Tipe             | Deskripsi                          |
| ----------------------- | ---------------- | ---------------------------------- |
| id                      | bigint PK        | Primary key                        |
| attempt_id              | bigint FK UNIQUE | Foreign key ke quiz_attempts       |
| total_tab_switches      | integer          | Total perpindahan tab              |
| total_face_violations   | integer          | Total pelanggaran wajah            |
| face_not_detected_count | integer          | Berapa kali wajah tidak terdeteksi |
| look_left_count         | integer          | Berapa kali menoleh kiri           |
| look_right_count        | integer          | Berapa kali menoleh kanan          |
| look_down_count         | integer          | Berapa kali menunduk               |
| look_up_count           | integer          | Berapa kali mendongak              |
| integrity_score         | decimal(5,2)     | Skor integritas 0-100              |
| risk_level              | enum             | Level risiko (low/medium/high)     |
| flagged_for_review      | boolean          | Flag untuk review instructor       |

### 5. camera_access_logs

**Tujuan:** Audit trail akses kamera

| Kolom                   | Tipe      | Deskripsi                    |
| ----------------------- | --------- | ---------------------------- |
| id                      | bigint PK | Primary key                  |
| attempt_id              | bigint FK | Foreign key ke quiz_attempts |
| permission_requested_at | timestamp | Waktu request permission     |
| permission_granted      | boolean   | Apakah diizinkan             |
| permission_granted_at   | timestamp | Waktu izin diberikan         |
| browser_info            | text      | Info browser (user agent)    |
| error_message           | text      | Pesan error jika gagal       |

## Ringkasan Relasi

```mermaid
graph LR
    A[quizzes] -->|1:1| B[quiz_security_settings]
    A -->|1:N| C[quiz_attempts]
    C -->|1:1| D[quiz_attempt_integrity_summary]
    C -->|1:N| E[quiz_attempt_question_order]
    C -->|1:N| F[monitoring_logs]
    C -->|1:N| G[camera_access_logs]
    C -->|1:N| H[student_answers]

    style A fill:#e1f5ff
    style B fill:#fff4e1
    style C fill:#e1f5ff
    style D fill:#ffe1e1
    style E fill:#e1ffe1
    style F fill:#ffe1e1
    style G fill:#f5e1ff
    style H fill:#e1f5ff
```

## Catatan Penting

1. **Fisher-Yates Shuffle**: Diimplementasikan saat quiz_attempt dibuat, hasil disimpan di `quiz_attempt_question_order`

2. **MediaPipe Face Mesh**: Berjalan di client-side (browser), mengirim violation ke backend via API

3. **Tab Detection**: Menggunakan JavaScript Page Visibility API, log dikirim real-time

4. **Integrity Score**: Dihitung saat quiz selesai berdasarkan total violations dengan formula penalty

5. **Storage**: Screenshot disimpan di `storage/app/monitoring_screenshots/` dengan naming convention `{attempt_id}_{timestamp}_{violation_type}.jpg`
