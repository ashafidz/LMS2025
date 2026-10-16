<nav class="rs-dash-group" aria-labelledby="nav-ringkasan">
    <h2 id="nav-ringkasan">Ringkasan</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('admin.dashboard')" :active="['admin.dashboard']">Beranda</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-pengguna">
    <h2 id="nav-pengguna">Pengguna</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('admin.instructor-application.index')" :active="['admin.instructor-application.*', 'admin.instructor-applications.*']">Pendaftar instruktur</x-dash.link>
        <x-dash.link :href="route('admin.manajemen-student.index')" :active="['admin.manajemen-student.*']">Siswa</x-dash.link>
        <x-dash.link :href="route('admin.course-enrollments.index')" :active="['admin.course-enrollments.*']">Peserta kursus</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-kursus">
    <h2 id="nav-kursus">Kursus</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('admin.publication.index')" :active="['admin.publication.*']">Publikasi kursus</x-dash.link>
        <x-dash.link :href="route('admin.course-categories.index')" :active="['admin.course-categories.*']">Kategori kursus</x-dash.link>
        <x-dash.link :href="route('admin.certificate-templates.index')" :active="['admin.certificate-templates.*']">Template sertifikat</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-penjualan">
    <h2 id="nav-penjualan">Penjualan dan hadiah</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('admin.coupons.index')" :active="['admin.coupons.*']">Kupon</x-dash.link>
        <x-dash.link :href="route('admin.point-sync.index')" :active="['admin.point-sync.*']">Sinkron poin</x-dash.link>
        <x-dash.link :href="route('admin.badges.index')" :active="['admin.badges.*']">Lencana</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-komunikasi">
    <h2 id="nav-komunikasi">Komunikasi</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('admin.contact-messages.index')" :active="['admin.contact-messages.*']">Pesan masuk</x-dash.link>
        <x-dash.link :href="route('admin.likert-questions.index')" :active="['admin.likert-questions.*']">Pertanyaan ulasan</x-dash.link>
    </ul>
</nav>
