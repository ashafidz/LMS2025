<x-mail::message>
# Halo, {{ $submission->user->name }}!

Instruktur telah meninjau tugas Anda untuk pelajaran **"{{ $submission->assignment->lesson->title }}"** di kursus **"{{ $submission->assignment->lesson->module->course->title }}"**.

Sayangnya, nilai Anda belum mencapai standar kelulusan dan tugas Anda memerlukan revisi.

**Nilai Anda:** {{ $submission->grade }} / 100
**Umpan Balik dari Instruktur:**
<x-mail::panel>
{!! nl2br(e($submission->feedback)) !!}
</x-mail::panel>

Silakan perbaiki tugas Anda sesuai dengan umpan balik di atas dan unggah kembali file revisi Anda.

<x-mail::button :url="route('student.courses.show', $submission->assignment->lesson->module->course->slug)">
Lihat Tugas Sekarang
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>