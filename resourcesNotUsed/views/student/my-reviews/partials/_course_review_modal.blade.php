{{-- resources/views/student/my-reviews/partials/_course_review_modal.blade.php --}}

<div class="modal fade" id="courseReviewModal-{{ $review->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('student.reviews.course.update', $review->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Ulasan untuk Kursus: {{ $review->course->title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group text-center">
                        <label class="d-block mb-2">Rating Keseluruhan</label>
                        <div class="star-rating">
                            @for ($i = 5; $i >= 1; $i--)
                            <input type="radio" id="course-star{{ $review->id }}-{{ $i }}" name="rating" value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'checked' : '' }} required/><label for="course-star{{ $review->id }}-{{ $i }}" title="{{ $i }} stars">★</label>
                            @endfor
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ulasan Anda</label>
                        <textarea name="comment" class="form-control" rows="4">{{ old('comment', $review->comment) }}</textarea>
                    </div>

                    @if($courseLikertQuestions->isNotEmpty())
                        <hr>
                        <label class="d-block mb-3">Penilaian Detail Kursus:</label>
                        @foreach($courseLikertQuestions as $question)
                        <div class="form-group likert-scale">
                            <p class="mb-1">{{ $question->question_text }}</p>
                            <div class="btn-group d-flex justify-content-between text-center" role="group">
                                @php $currentAnswer = $userLikertAnswers[$question->id] ?? 0; @endphp
                                <div class="w-25 d-flex flex-column justify-content-center">
                                    <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="cl-{{ $review->id }}-{{ $question->id }}-1" value="1" {{ $currentAnswer == 1 ? 'checked' : '' }} required><label class="" for="cl-{{ $review->id }}-{{ $question->id }}-1">Sangat Tidak Setuju</label>
                                </div>
                                <div class="w-25 d-flex flex-column justify-content-center">
                                    <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="cl-{{ $review->id }}-{{ $question->id }}-2" value="2" {{ $currentAnswer == 2 ? 'checked' : '' }} required><label class="" for="cl-{{ $review->id }}-{{ $question->id }}-2">Tidak Setuju</label>
                                </div>
                                <div class="w-25 d-flex flex-column justify-content-center">
                                    <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="cl-{{ $review->id }}-{{ $question->id }}-3" value="3" {{ $currentAnswer == 3 ? 'checked' : '' }} required><label class="" for="cl-{{ $review->id }}-{{ $question->id }}-3">Setuju</label>
                                </div>
                                <div class="w-25 d-flex flex-column justify-content-center">
                                    <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="cl-{{ $review->id }}-{{ $question->id }}-4" value="4" {{ $currentAnswer == 4 ? 'checked' : '' }} required><label class="" for="cl-{{ $review->id }}-{{ $question->id }}-4">Sangat Setuju</label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>