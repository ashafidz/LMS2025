{{-- resources/views/student/courses/partials/_review_form.blade.php --}}

{{-- Tampilkan pesan error jika ada --}}
<div id="review-error-alert" class="alert alert-danger" style="display: none;"></div>


    <form id="course-review-form" action="{{ route('student.course.review.store', $course->id) }}" method="POST">
        @csrf
        {{-- Rating & Ulasan untuk Kursus --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Bagaimana Penilaian Anda Tentang Kursus Ini?</h5>
            </div>
            <div class="card-block">
                <div class="form-group">
                    <label>Rating Kursus</label>
                    <br />
                    <div class="star-rating">
                        @for ($i = 5; $i >= 1; $i--)
                        <input type="radio" id="course-star{{ $i }}" name="course_rating" value="{{ $i }}" required/><label for="course-star{{ $i }}" title="{{ $i }} stars">★</label>
                        @endfor
                    </div>
                </div>
                <div class="form-group">
                    <label for="course_comment">Tulis Ulasan Anda (Opsional)</label>
                    <textarea name="course_comment" id="course_comment" class="form-control" rows="4" placeholder="Bagikan pengalaman belajar Anda di kursus ini..."></textarea>
                </div>
                @if($courseLikertQuestions->isNotEmpty())
                    <hr>
                    <label class="d-block mb-3">Penilaian Detail Kursus:</label>
                    @foreach($courseLikertQuestions as $question)
                    <div class="form-group likert-scale">
                        <p class="mb-1">{{ $question->question_text }}</p>
                        <div class="btn-group d-flex justify-content-between text-center" role="group">
                            <div class="w-25 d-flex flex-column justify-content-center">
                                <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="likert-{{ $question->id }}-1" value="1" autocomplete="off" required>
                                <label class="" for="likert-{{ $question->id }}-1">
                                    Sangat Tidak Setuju
                                </label>
                            </div>
                            <div class="w-25 d-flex flex-column justify-content-center">
                                <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="likert-{{ $question->id }}-2" value="2" autocomplete="off" required>
                                <label class="" for="likert-{{ $question->id }}-2">                                
                                    Tidak Setuju
                                </label>
                            </div>
                            <div class="w-25 d-flex flex-column justify-content-center">
                                <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="likert-{{ $question->id }}-3" value="3" autocomplete="off" required>
                                <label class="" for="likert-{{ $question->id }}-3">
                                    Setuju
                                </label>
                            </div>
                            <div class="w-25 d-flex flex-column justify-content-center">
                                <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="likert-{{ $question->id }}-4" value="4" autocomplete="off" required>
                                <label class="" for="likert-{{ $question->id }}-4">
                                    Sangat Setuju
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Rating & Ulasan untuk Instruktur --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Bagaimana Penilaian Anda Tentang Instruktur?</h5>
                <p class="mb-0 text-muted">Instruktur: {{ $course->instructor->name }}</p>
            </div>
            <div class="card-block">
                <div class="form-group">
                    <label>Rating Instructor</label>
                    <br />
                    <div class="star-rating">
                        @for ($i = 5; $i >= 1; $i--)
                        <input type="radio" id="instructor-star{{ $i }}" name="instructor_rating" value="{{ $i }}" required/><label for="instructor-star{{ $i }}" title="{{ $i }} stars">★</label>
                        @endfor
                    </div>
                </div>
                <div class="form-group">
                    <label for="instructor_comment">Tulis Ulasan Anda (Opsional)</label>
                    <textarea name="instructor_comment" id="instructor_comment" class="form-control" rows="4" placeholder="Bagikan pendapat Anda tentang cara instruktur mengajar..."></textarea>
                </div>
                @if($instructorLikertQuestions->isNotEmpty())
                    <hr>
                    <label class="d-block mb-3">Penilaian Detail Instruktur:</label>
                    @foreach($instructorLikertQuestions as $question)
                    <div class="form-group likert-scale">
                        <p class="mb-1">{{ $question->question_text }}</p>
                        <div class="btn-group d-flex justify-content-between text-center" role="group">
                            <div class="w-25 d-flex flex-column justify-content-center">
                                <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="likert-{{ $question->id }}-1" value="1" autocomplete="off" required>
                                <label class="" for="likert-{{ $question->id }}-1">Sangat Tidak Setuju</label>
                            </div>
                            <div class="w-25 d-flex flex-column justify-content-center">
                                <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="likert-{{ $question->id }}-2" value="2" autocomplete="off" required>
                                <label class="" for="likert-{{ $question->id }}-2">Tidak Setuju</label>
                            </div>
                            <div class="w-25 d-flex flex-column justify-content-center">
                                <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="likert-{{ $question->id }}-3" value="3" autocomplete="off" required>
                                <label class="" for="likert-{{ $question->id }}-3">Setuju</label>
                            </div>
                            <div class="w-25 d-flex flex-column justify-content-center">
                                <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="likert-{{ $question->id }}-4" value="4" autocomplete="off" required>
                                <label class="" for="likert-{{ $question->id }}-4">Sangat Setuju</label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">Kirim Ulasan</button>
        </div>
    </form>


{{-- Styling untuk rating bintang dan skala Likert --}}
<style>
    .star-rating { display: inline-block; direction: rtl; }
    .star-rating input { display: none; }
    .star-rating label { font-size: 2.5rem; color: #ddd; cursor: pointer; }
    .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #f5b301; }
    .likert-scale .btn-check:checked+.btn { background-color: #007bff; color: white; }
</style>