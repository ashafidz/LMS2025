<nav class="rs-dash-group" aria-labelledby="nav-belajar">
    <h2 id="nav-belajar">Belajar</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('student.dashboard')" :active="['student.dashboard']">Beranda</x-dash.link>
        <x-dash.link :href="route('student.my_courses')" :active="['student.my_courses', 'student.courses.*', 'student.course.*', 'student.module.*', 'student.lessons.*', 'student.quiz.*', 'student.assignment.*']">Kursus saya</x-dash.link>
        <x-dash.link :href="route('student.certificates.index')" :active="['student.certificates.*', 'student.certificate.*']">Sertifikat</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-pencapaian">
    <h2 id="nav-pencapaian">Pencapaian</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('student.points.index')" :active="['student.points.*', 'student.diamonds.*']">Poin dan diamond</x-dash.link>
        <x-dash.link :href="route('student.badges.index')" :active="['student.badges.*']">Lencana</x-dash.link>
        <x-dash.link :href="route('student.reviews.index')" :active="['student.reviews.*']">Ulasan saya</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-akun">
    <h2 id="nav-akun">Akun</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('student.transactions.index')" :active="['student.transactions.*']">Riwayat transaksi</x-dash.link>
        <x-dash.link :href="route('student.cart.index')" :active="['student.cart.*']">Keranjang</x-dash.link>
    </ul>
</nav>
