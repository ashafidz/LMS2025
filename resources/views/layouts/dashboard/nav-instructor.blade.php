<nav class="rs-dash-group" aria-labelledby="nav-mengajar">
    <h2 id="nav-mengajar">Mengajar</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('instructor.dashboard')" :active="['instructor.dashboard']">Beranda</x-dash.link>
        <x-dash.link :href="route('instructor.courses.index')" :active="['instructor.courses.*', 'instructor.modules.*', 'instructor.lessons.*', 'instructor.lesson_points.*', 'instructor.quiz.*', 'instructor.quizzes.*', 'instructor.assignment.*', 'instructor.submission.*', 'instructor.recap.*', 'instructor.course.*', 'instructor.module.*']">Kursus saya</x-dash.link>
        <x-dash.link :href="route('instructor.question-bank.topics.index')" :active="['instructor.question-bank.*']">Bank soal</x-dash.link>
    </ul>
</nav>
<a class="rs-dash-btn" href="{{ route('instructor.courses.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i>Buat kursus baru</a>
