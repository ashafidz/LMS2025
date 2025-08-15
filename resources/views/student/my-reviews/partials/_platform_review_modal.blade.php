{{-- resources/views/student/my-reviews/partials/_platform_review_modal.blade.php --}}

<div class="modal fade" id="platformReviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('student.reviews.platform.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ulasan Anda Tentang Platform</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p style="font-size: 1rem;">Bagikan pengalaman Anda menggunakan platform kami secara keseluruhan.</p>
                    
                    {{-- Rating Bintang --}}
                    <div class="form-group text-center">
                        <label class="d-block mb-2">Rating Keseluruhan</label>
                        <div class="star-rating">
                            @for ($i = 5; $i >= 1; $i--)
                            <input type="radio" id="platform-star{{ $i }}" name="rating" value="{{ $i }}" {{ old('rating', $platformReview->rating ?? 0) == $i ? 'checked' : '' }} required/>
                            <label for="platform-star{{ $i }}" title="{{ $i }} stars">★</label>
                            @endfor
                        </div>
                    </div>

                    {{-- Komentar --}}
                    <div class="form-group">
                        <label for="platform_comment">Tulis Ulasan Anda (Opsional)</label>
                        <textarea name="comment" id="platform_comment" class="form-control" rows="4">{{ old('comment', $platformReview->comment ?? '') }}</textarea>
                    </div>

                    {{-- Pertanyaan Skala Likert --}}
                    @if($platformLikertQuestions->isNotEmpty())
                        <hr>
                        <label class="d-block mb-3" style="font-size: 1rem; font-weight: 500;">Penilaian Detail:</label>


                        @foreach($platformLikertQuestions as $question)
                        <div class="form-group likert-scale">
                            <p class="likert-question">{{ $question->question_text }}</p>
                            <div class="btn-group d-flex justify-content-between text-center" role="group">
                                @php $currentAnswer = $userLikertAnswers[$question->id] ?? 0; @endphp
                                <div class="w-25 d-flex flex-column justify-content-center" >
                                    <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="pl-{{ $question->id }}-1" value="1" {{ $currentAnswer == 1 ? 'checked' : '' }} required><label class="" for="pl-{{ $question->id }}-1">Sangat Tidak Setuju</label>
                                </div>
                                <div class="w-25 d-flex flex-column justify-content-center" >
                                    <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="pl-{{ $question->id }}-2" value="2" {{ $currentAnswer == 2 ? 'checked' : '' }} required><label class="" for="pl-{{ $question->id }}-2">Tidak Setuju</label>
                                </div>
                                <div class="w-25 d-flex flex-column justify-content-center" >
                                    <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="pl-{{ $question->id }}-3" value="3" {{ $currentAnswer == 3 ? 'checked' : '' }} required><label class="" for="pl-{{ $question->id }}-3">Setuju</label>
                                </div>
                                <div class="w-25 d-flex flex-column justify-content-center" >
                                    <input type="radio" class="btn-check" name="likert_answers[{{ $question->id }}]" id="pl-{{ $question->id }}-4" value="4" {{ $currentAnswer == 4 ? 'checked' : '' }} required><label class="" for="pl-{{ $question->id }}-4">Sangat Setuju</label>
                                </div>



                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Ulasan</button>
                </div>
            </form>
            <style>
            .likert-question {
                font-size: 1rem !important; /* pastikan lebih besar */
                color: #555 !important;       /* warna hitam pekat */
                margin-bottom: 0.5rem;        /* beri jarak bawah */
            }
            </style>

        </div>
    </div>
</div>