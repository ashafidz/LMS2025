<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sertifikat - {{ $course->title }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', sans-serif;
        }
        .certificate-container {
            position: relative;
            width: 297mm;
            height: 210mm;
        }
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 25mm 20mm;
            box-sizing: border-box;
        }
        /* Penempatan Teks */
        .platform-name {
            position: absolute;
            top: 25mm;
            left: 20mm;
            font-size: 18px;
        }
        .certificate-code {
            position: absolute;
            top: 25mm;
            right: 20mm;
            font-size: 14px;
            color: #555;
        }
        .completion-text {
            position: absolute;
            top: 55mm;
            left: 20mm;
            font-size: 12px;
            letter-spacing: 1px;
            color: #333;
        }
        .course-title {
            position: absolute;
            top: 62mm;
            left: 20mm;
            font-size: 36px;
            font-weight: bold;
            color: #000;
            max-width: 180mm;
            line-height: 1.2;
        }
        .instructor-name {
            position: absolute;
            top: 95mm;
            left: 20mm;
            font-size: 14px;
            color: #555;
        }
        .student-name {
            position: absolute;
            top: 130mm;
            left: 20mm;
            font-size: 40px;
            font-weight: bold;
            color: #000;
        }
        .issue-date {
            position: absolute;
            top: 150mm;
            left: 20mm;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    @php $settings = \App\Models\SiteSetting::first(); @endphp
    <div class="certificate-container">
        <!-- Gambar Latar Belakang -->
        <img src="{{ public_path('assets/images/certificate-background.png') }}" class="background-image">

        <!-- Konten Teks yang Ditumpuk di Atas -->
        <div class="content">
            <div class="platform-name">
                @if($settings->logo_path)
                    <img src="{{ public_path('storage/' . $settings->logo_path) }}" alt="Logo" style="height: 24px;">
                @else
                    <strong>{{ $settings->site_name }}</strong>
                @endif
            </div>

            <div class="certificate-code">LMS-1-26-1755230460</div>

            <div class="completion-text">CERTIFICATE OF COMPLETION</div>

            <div class="course-title">Digital Marketing (Jurusan Teknologi Multimedia Kreatif)</div>

            <div class="instructor-name">Instructors <strong>Ashafidz Fauzan Dianta</strong></div>

            <div class="student-name">Akhmad Nabil Gibran</div>

            <div class="issue-date">Date <strong>16 August 2025</strong></div>
        </div>
    </div>
</body>
</html>