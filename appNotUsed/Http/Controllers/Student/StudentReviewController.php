<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseReview;
use App\Models\InstructorReview;
use App\Models\LikertQuestion;
use App\Models\PlatformReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentReviewController extends Controller
{
    /**
     * Menampilkan halaman utama "Kelola Ulasan".
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil ulasan platform jika ada
        $platformReview = $user->platformReview;

        // Ambil semua ulasan kursus dan instruktur dari pengguna
        $courseReviews = $user->courseReviews()->with('course')->latest()->paginate(5, ['*'], 'course_reviews_page');
        $instructorReviews = $user->instructorReviews()->with('instructor', 'course')->latest()->paginate(5, ['*'], 'instructor_reviews_page');

        // Ambil semua pertanyaan Likert yang aktif
        $likertQuestions = LikertQuestion::where('is_active', true)->get();
        $courseLikertQuestions = $likertQuestions->where('category', 'course');
        $instructorLikertQuestions = $likertQuestions->where('category', 'instructor');
        $platformLikertQuestions = $likertQuestions->where('category', 'platform');

        // Ambil jawaban likert dari user
        $userLikertAnswers = $user->likertAnswers->pluck('answer', 'likert_question_id');

        return view('student.my-reviews.index', compact(
            'platformReview',
            'courseReviews',
            'instructorReviews',
            'courseLikertQuestions',
            'instructorLikertQuestions',
            'platformLikertQuestions',
            'userLikertAnswers'
        ));
    }

    /**
     * Menyimpan atau memperbarui ulasan platform.
     */
    public function storeOrUpdatePlatformReview(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'likert_answers' => 'nullable|array',
            'likert_answers.*' => 'required|integer|min:1|max:4',
        ]);

        DB::transaction(function () use ($user, $validated) {
            // Simpan/update ulasan utama platform
            $user->platformReview()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'],
                ]
            );

            // Simpan/update jawaban likert untuk platform
            if (!empty($validated['likert_answers'])) {
                foreach ($validated['likert_answers'] as $questionId => $answer) {
                    $user->likertAnswers()->updateOrCreate(
                        ['likert_question_id' => $questionId, 'course_id' => null], // course_id null untuk platform
                        ['answer' => $answer]
                    );
                }
            }
        });

        return back()->with('success', 'Ulasan platform berhasil disimpan.');
    }

    /**
     * Memperbarui ulasan kursus yang sudah ada.
     */
    public function updateCourseReview(Request $request, CourseReview $review)
    {
        // Pastikan pengguna hanya bisa mengedit ulasannya sendiri
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'likert_answers' => 'nullable|array',
            'likert_answers.*' => 'required|integer|min:1|max:4',
        ]);

        DB::transaction(function () use ($review, $validated) {
            $review->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            if (!empty($validated['likert_answers'])) {
                foreach ($validated['likert_answers'] as $questionId => $answer) {
                    Auth::user()->likertAnswers()->updateOrCreate(
                        ['likert_question_id' => $questionId, 'course_id' => $review->course_id],
                        ['answer' => $answer]
                    );
                }
            }
        });

        return back()->with('success', 'Ulasan kursus berhasil diperbarui.');
    }

    /**
     * Memperbarui ulasan instruktur yang sudah ada.
     */
    public function updateInstructorReview(Request $request, InstructorReview $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'likert_answers' => 'nullable|array',
            'likert_answers.*' => 'required|integer|min:1|max:4',
        ]);

        DB::transaction(function () use ($review, $validated) {
            $review->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            if (!empty($validated['likert_answers'])) {
                foreach ($validated['likert_answers'] as $questionId => $answer) {
                    Auth::user()->likertAnswers()->updateOrCreate(
                        ['likert_question_id' => $questionId, 'course_id' => $review->course_id],
                        ['answer' => $answer]
                    );
                }
            }
        });

        return back()->with('success', 'Ulasan instruktur berhasil diperbarui.');
    }
}
