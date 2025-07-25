<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sertifikat Kelulusan - {{ $course->title }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .certificate-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: white;
            box-sizing: border-box;
            padding: 20mm;
            border: 10px solid #1e3a8a; /* Warna biru tua untuk border */
        }
        .content {
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .logo {
            width: 80px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 42px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
        }
        .subtitle {
            font-size: 20px;
            color: #666;
            margin-top: 5px;
        }
        .presented-to {
            font-size: 18px;
            margin-top: 40px;
            color: #555;
        }
        .student-name {
            font-size: 36px;
            font-weight: bold;
            color: #000;
            margin: 10px 0;
            border-bottom: 2px solid #ccc;
            display: inline-block;
            padding-bottom: 5px;
        }
        .description {
            font-size: 16px;
            color: #555;
            margin-top: 20px;
        }
        .course-name {
            font-weight: bold;
            font-size: 18px;
        }
        .footer {
            position: absolute;
            bottom: 30mm;
            width: calc(100% - 40mm);
            left: 20mm;
            display: table;
            width: 100%;
        }
        .signature-box {
            width: 33.33%;
            text-align: center;
            display: table-cell;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin: 40px auto 5px auto;
            width: 200px;
        }
        .certificate-id {
            position: absolute;
            bottom: 15mm;
            left: 20mm;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    @php $settings = \App\Models\SiteSetting::first(); @endphp
    <div class="certificate-container">
        <div class="content">
            @if($settings && $settings->logo_path)
                <img src="{{ public_path('storage/' . $settings->logo_path) }}" class="logo" alt="Logo">
            @endif

            <h1 class="title">SERTIFIKAT KELULUSAN</h1>
            <p class="subtitle">Certificate of Completion</p>

            <p class="presented-to">Diberikan Kepada:</p>
            <div class="student-name">{{ $user->name }}</div>

            <p class="description">
                Telah berhasil menyelesaikan kursus online:
                <br>
                <span class="course-name">"{{ $course->title }}"</span>
            </p>
        </div>
        
        <div class="footer">
            <div class="signature-box">
                {{-- Tanda tangan Instruktur --}}
                <div class="signature-line"></div>
                <strong>{{ $course->instructor->name }}</strong><br>
                <span style="font-size: 12px; color: #666;">Instruktur</span>
            </div>
            <div class="signature-box">
                {{-- Tanggal --}}
                <p><strong>Tanggal Kelulusan</strong></p>
                <p>{{ $certificate->issued_at->format('d F Y') }}</p>
            </div>
            <div class="signature-box">
                {{-- Tanda tangan CEO/Pimpinan --}}
                <div class="signature-line"></div>
                <strong>{{-- Nama CEO di sini --}}</strong><br>
                <span style="font-size: 12px; color: #666;">CEO {{ $settings->site_name }}</span>
            </div>
        </div>
        
        <div class="certificate-id">
            Kode Sertifikat: {{ $certificate->certificate_code }}
        </div>
    </div>
</body>
</html>