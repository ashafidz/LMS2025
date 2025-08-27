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
        right: 20%; 
        font-size: 1.4vw; 
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
            font-size: 40px;
            font-weight: bold;
            color: #000;
            max-width: 220mm;
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
    <div class="certificate-container">
        <img src="{{ public_path('assets/images/certificate-background.png') }}" class="background-image" onerror="this.style.display='none'">
                @php $settings = \App\Models\SiteSetting::first(); @endphp

        <!-- All your text content goes here -->
        <div class="content">
            <div class="platform-name">
                
                @if(isset($settings) && $settings->logo_path)
                    <img src="{{ public_path('storage/' . $settings->logo_path) }}" alt="Logo" style="height: 24px;">
                @elseif(isset($settings))
                    <strong>{{ $settings->site_name }}</strong>
                @else
                    <strong>Platform Name</strong>
                @endif
            </div>

            <div class="certificate-code">{{ $certificate->certificate_code }}</div>
            
            <div class="completion-text">CERTIFICATION OF COMPLETION</div>

            <div class="course-title">
                {{ $course->title }}
            </div>

            <div class="instructor-name">Instructors <strong>{{ $course->instructor->name }}</strong></div>

            <div class="student-name">{{ $user->name }}</div>
            
            <div class="issue-date">Date <strong>{{ $certificate->issued_at->format('d F Y') }}</strong></div>
        </div>
    </div>
</body>
</html>
