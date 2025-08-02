{{-- resources/views/student/courses/partials/_locked_content.blade.php --}}
<div class="text-center p-5">
    <i class="fa fa-lock fa-4x text-muted mb-3"></i>
    <h4 class="font-weight-bold">Modul Ini Terkunci</h4>
    <p class="text-muted">
        Anda harus mengumpulkan setidaknya <strong>{{ $module->points_required }} poin</strong> di kursus ini untuk membuka modul "{{ $module->title }}".
        <br>
        Teruslah belajar dan selesaikan pelajaran di modul sebelumnya untuk mendapatkan poin!
    </p>
</div>