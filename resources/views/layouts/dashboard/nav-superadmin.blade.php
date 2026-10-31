<nav class="rs-dash-group" aria-labelledby="nav-ringkasan">
    <h2 id="nav-ringkasan">Ringkasan</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('superadmin.dashboard')" :active="['superadmin.dashboard*']">Beranda</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-pengguna">
    <h2 id="nav-pengguna">Pengguna</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('superadmin.instructor-application.index')" :active="['superadmin.instructor-application.*', 'superadmin.instructor-applications.*']">Pendaftar instruktur</x-dash.link>
        <x-dash.link :href="route('superadmin.manajemen-student.index')" :active="['superadmin.manajemen-student.*']">Siswa</x-dash.link>
        <x-dash.link :href="route('superadmin.course-enrollments.index')" :active="['superadmin.course-enrollments.*']">Peserta kursus</x-dash.link>
        <x-dash.link :href="route('superadmin.admins.index')" :active="['superadmin.admins.*']">Admin</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-kursus">
    <h2 id="nav-kursus">Kursus</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('superadmin.publication.index')" :active="['superadmin.publication.*']">Publikasi kursus</x-dash.link>
        <x-dash.link :href="route('superadmin.course-categories.index')" :active="['superadmin.course-categories.*']">Kategori kursus</x-dash.link>
        <x-dash.link :href="route('superadmin.certificate-templates.index')" :active="['superadmin.certificate-templates.*']">Template sertifikat</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-penjualan">
    <h2 id="nav-penjualan">Penjualan dan hadiah</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('superadmin.coupons.index')" :active="['superadmin.coupons.*']">Kupon</x-dash.link>
        <x-dash.link :href="route('superadmin.point-sync.index')" :active="['superadmin.point-sync.*']">Sinkron poin</x-dash.link>
        <x-dash.link :href="route('superadmin.badges.index')" :active="['superadmin.badges.*']">Lencana</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-komunikasi">
    <h2 id="nav-komunikasi">Komunikasi</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('superadmin.contact-messages.index')" :active="['superadmin.contact-messages.*']">Pesan masuk</x-dash.link>
        <x-dash.link :href="route('superadmin.likert-questions.index')" :active="['superadmin.likert-questions.*']">Pertanyaan ulasan</x-dash.link>
    </ul>
</nav>
<nav class="rs-dash-group" aria-labelledby="nav-sistem">
    <h2 id="nav-sistem">Sistem</h2>
    <ul class="rs-dash-toc">
        <x-dash.link :href="route('superadmin.settings.edit')" :active="['superadmin.settings.*']">Pengaturan situs</x-dash.link>
    </ul>
</nav>
