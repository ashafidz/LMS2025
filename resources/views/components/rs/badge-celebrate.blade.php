@props(['badges'])
<script>
    // Badge baru: modal perayaan. Judul dan deskripsi disisipkan sebagai teks, bukan HTML.
    // window.rsModalGate menahan antrean toast (x-rs.flash-toast) sampai modal ini ditutup.
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Swal) { return; }
        var badges = {{ \Illuminate\Support\Js::from($badges) }};
        var box = document.createElement('div');
        badges.forEach(function (badge) {
            var item = document.createElement('div');
            item.className = 'rs-swal-badge__item';
            var medal = document.createElement('div');
            medal.className = 'rs-swal-badge__medal';
            var rays = document.createElement('span');
            rays.className = 'rs-swal-badge__rays';
            rays.setAttribute('aria-hidden', 'true');
            var image = document.createElement('img');
            image.src = badge.icon;
            image.alt = '';
            image.width = 112;
            image.height = 112;
            medal.appendChild(rays);
            medal.appendChild(image);
            var title = document.createElement('div');
            title.className = 'rs-swal-badge__title';
            title.textContent = badge.title;
            var description = document.createElement('div');
            description.className = 'rs-swal-badge__desc';
            description.textContent = badge.description;
            item.appendChild(medal);
            item.appendChild(title);
            item.appendChild(description);
            box.appendChild(item);
        });
        window.rsModalGate = Swal.fire({
            titleText: badges.length > 1
                ? 'Selamat! Kamu dapat ' + badges.length + ' badge baru'
                : 'Selamat! Kamu dapat badge baru',
            html: box,
            customClass: { popup: 'rs-swal-badge' },
            confirmButtonText: 'Lihat Badge Saya',
            showCancelButton: true,
            cancelButtonText: 'Nanti saja',
            reverseButtons: true,
            didOpen: function () {
                if (typeof rsCelebrate === 'function') { rsCelebrate({ zIndex: 1070 }); }
            }
        }).then(function (result) {
            if (result.isConfirmed) { window.location.href = {{ \Illuminate\Support\Js::from(route('student.badges.index')) }}; }
        });
    });
</script>
