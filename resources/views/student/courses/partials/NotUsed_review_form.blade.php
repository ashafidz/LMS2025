{{-- resources/views/student/courses/partials/_review_form.blade.php --}}

{{-- Pastikan Anda sudah memuat Font Awesome di layout utama Anda --}}
{{-- Contoh: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/> --}}

{{-- Tampilkan pesan error jika ada --}}
<div id="review-error-alert" class="alert alert-danger" style="display: none;"></div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Form Penilaian & Ulasan</h5>
    </div>
    <div class="card-body">
        <form id="course-review-form" action="{{ route('student.course.review.store', $course->id) }}" method="POST">
            @csrf

            {{-- Bagian Penilaian Kursus --}}
            <h6 class="fw-bold">Penilaian Kursus</h6>
            <hr class="mt-2 mb-3">

            <div class="mb-4">
                <label class="form-label fw-semibold">Rating Keseluruhan</label>
                <div class="js-star-rating" data-input-name="course_rating">
                    <i class="fa-regular fa-star" data-value="1" title="Sangat Buruk"></i>
                    <i class="fa-regular fa-star" data-value="2" title="Buruk"></i>
                    <i class="fa-regular fa-star" data-value="3" title="Cukup"></i>
                    <i class="fa-regular fa-star" data-value="4" title="Baik"></i>
                    <i class="fa-regular fa-star" data-value="5" title="Sangat Baik"></i>
                </div>
                <input type="hidden" name="course_rating" required>
            </div>

            <div class="mb-4">
                <label for="course_comment" class="form-label fw-semibold">Ulasan Anda (Opsional)</label>
                <textarea name="course_comment" id="course_comment" class="form-control" rows="4" placeholder="Bagikan pengalaman belajar Anda di kursus ini..."></textarea>
            </div>

            @if($courseLikertQuestions->isNotEmpty())
            <div class="mb-4">
                <label class="form-label fw-semibold">Aspek Penilaian Detail</label>
                @foreach($courseLikertQuestions as $question)
                <div class="mb-3">
                    <p class="fs-6 mb-2">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                    <div class="btn-group w-100" role="group" aria-label="Likert Scale">
                        <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="course-likert-{{ $question->id }}-1" value="1" required>
                        <label class="btn btn-outline-primary" for="course-likert-{{ $question->id }}-1">Sangat Tidak Setuju</label>

                        <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="course-likert-{{ $question->id }}-2" value="2" required>
                        <label class="btn btn-outline-primary" for="course-likert-{{ $question->id }}-2">Tidak Setuju</label>

                        <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="course-likert-{{ $question->id }}-3" value="3" required>
                        <label class="btn btn-outline-primary" for="course-likert-{{ $question->id }}-3">Setuju</label>

                        <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="course-likert-{{ $question->id }}-4" value="4" required>
                        <label class="btn btn-outline-primary" for="course-likert-{{ $question->id }}-4">Sangat Setuju</label>
                    </div>
                </div>
                @endforeach
            </div>
            @endif


            {{-- Bagian Penilaian Instruktur --}}
            <h6 class="fw-bold mt-5">Penilaian Instruktur: <span class="fw-normal">{{ $course->instructor->name }}</span></h6>
            <hr class="mt-2 mb-3">

            <div class="mb-4">
                <label class="form-label fw-semibold">Rating Keseluruhan</label>
                <div class="js-star-rating" data-input-name="instructor_rating">
                    <i class="fa-regular fa-star" data-value="1" title="Sangat Buruk"></i>
                    <i class="fa-regular fa-star" data-value="2" title="Buruk"></i>
                    <i class="fa-regular fa-star" data-value="3" title="Cukup"></i>
                    <i class="fa-regular fa-star" data-value="4" title="Baik"></i>
                    <i class="fa-regular fa-star" data-value="5" title="Sangat Baik"></i>
                </div>
                <input type="hidden" name="instructor_rating" required>
            </div>

            <div class="mb-4">
                <label for="instructor_comment" class="form-label fw-semibold">Ulasan Anda (Opsional)</label>
                <textarea name="instructor_comment" id="instructor_comment" class="form-control" rows="4" placeholder="Bagikan pendapat Anda tentang cara instruktur mengajar..."></textarea>
            </div>

            @if($instructorLikertQuestions->isNotEmpty())
            <div class="mb-4">
                <label class="form-label fw-semibold">Aspek Penilaian Detail</label>
                @foreach($instructorLikertQuestions as $question)
                <div class="mb-3">
                    <p class="fs-6 mb-2">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                    <div class="btn-group w-100" role="group" aria-label="Likert Scale">
                        <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="inst-likert-{{ $question->id }}-1" value="1" required>
                        <label class="btn btn-outline-primary" for="inst-likert-{{ $question->id }}-1">Sangat Tidak Setuju</label>

                        <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="inst-likert-{{ $question->id }}-2" value="2" required>
                        <label class="btn btn-outline-primary" for="inst-likert-{{ $question->id }}-2">Tidak Setuju</label>

                        <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="inst-likert-{{ $question->id }}-3" value="3" required>
                        <label class="btn btn-outline-primary" for="inst-likert-{{ $question->id }}-3">Setuju</label>

                        <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="inst-likert-{{ $question->id }}-4" value="4" required>
                        <label class="btn btn-outline-primary" for="inst-likert-{{ $question->id }}-4">Sangat Setuju</label>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-5">Kirim Ulasan</button>
            </div>
        </form>
    </div>
</div>

{{-- CSS & JavaScript --}}
<style>
    .js-star-rating i {
        font-size: 2rem;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s ease-in-out;
    }
    .js-star-rating i.filled,
    .js-star-rating:hover i.hover {
        color: #f5b301;
    }
    .js-star-rating:hover i.hover ~ i {
        color: #ddd;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fungsi untuk inisialisasi rating bintang
    function initializeStarRating(container) {
        const stars = container.querySelectorAll('i');
        const hiddenInput = container.parentElement.querySelector('input[type="hidden"]');
        
        stars.forEach(star => {
            // Event saat mouse berada di atas bintang
            star.addEventListener('mouseover', function () {
                stars.forEach(s => s.classList.remove('hover')); // Hapus semua hover
                this.classList.add('hover');
                let prev = this.previousElementSibling;
                while (prev) {
                    prev.classList.add('hover');
                    prev = prev.previousElementSibling;
                }
            });

            // Event saat mouse keluar dari area rating
            container.addEventListener('mouseout', function () {
                stars.forEach(s => s.classList.remove('hover'));
            });

            // Event saat bintang diklik
            star.addEventListener('click', function () {
                const value = this.getAttribute('data-value');
                hiddenInput.value = value;

                // Update tampilan bintang
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('filled');
                        s.classList.remove('fa-regular');
                        s.classList.add('fa-solid');
                    } else {
                        s.classList.remove('filled');
                        s.classList.remove('fa-solid');
                        s.classList.add('fa-regular');
                    }
                });
            });
        });
    }

    // Terapkan fungsi ke semua elemen rating bintang
    document.querySelectorAll('.js-star-rating').forEach(container => {
        initializeStarRating(container);
    });

    // Validasi sebelum submit
    const form = document.getElementById('course-review-form');
    form.addEventListener('submit', function(e) {
        const courseRating = form.querySelector('input[name="course_rating"]').value;
        const instructorRating = form.querySelector('input[name="instructor_rating"]').value;
        
        if (!courseRating || !instructorRating) {
            e.preventDefault(); // Hentikan submit
            const errorAlert = document.getElementById('review-error-alert');
            errorAlert.textContent = 'Harap berikan rating untuk kursus dan instruktur.';
            errorAlert.style.display = 'block';
            window.scrollTo(0, 0); // Scroll ke atas untuk melihat error
        }
    });
});
</script>
@endpush