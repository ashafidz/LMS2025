<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai - {{ $module->title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <h2>Rekap Nilai: {{ $module->title }}</h2>
    <p>Kursus: {{ $module->course->title }}</p>
    <table>
        <thead>
            <tr>
                <th class="text-left">NIM/NIP</th>
                <th class="text-left">Nama</th>
                <th>Poin Kursus</th>
                <th>Poin Modul</th>
                @foreach($gradableLessons as $lesson)
                    <th>{{ $lesson->title }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
            <tr>
                <td class="text-left">{{ $student->studentProfile->unique_id_number ?? '-' }}</td>
                <td class="text-left">{{ $student->name }}</td>
                <td>{{ $scores[$student->id]['total_course_points'] }}</td>
                <td>{{ $scores[$student->id]['total_module_points'] }}</td>
                @foreach($gradableLessons as $lesson)
                    <td>{{ $scores[$student->id][$lesson->id] }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>