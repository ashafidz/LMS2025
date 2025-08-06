<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LikertQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseReviewController extends Controller
{
    /**
     * Menampilkan form untuk membuat ulasan baru (via AJAX).
     */
    public function create(Course $course)
    {
        $user = Auth::user();
        // Logika untuk cek apakah user usah mengirimkan feedback terhadap kursus dan instructur di kursus tersebut
        $hasSentFeedback = false;
        if ($user) {
            $hasSentFeedback = $course->reviews()->where('user_id', $user->id)->exists();

            if ($hasSentFeedback) {
                $html = view('student.courses.partials._has_feedback', compact('course'))->render();

                return response()->json(['success' => true, 'html' => $html]);
            }
        }


        // Ambil semua pertanyaan Likert yang aktif
        $likertQuestions = LikertQuestion::where('is_active', true)->get();

        $courseLikertQuestions = $likertQuestions->where('category', 'course');
        $instructorLikertQuestions = $likertQuestions->where('category', 'instructor');

        // Render partial view dan kirim sebagai respons
        $html = view('student.courses.partials._review_form', compact(
            'course',
            'courseLikertQuestions',
            'instructorLikertQuestions'
        ))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Menyimpan ulasan baru ke database.
     */
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        // Validasi data yang masuk
        $validated = $request->validate([
            'course_rating' => 'required|integer|min:1|max:5',
            'course_comment' => 'nullable|string',
            'instructor_rating' => 'required|integer|min:1|max:5',
            'instructor_comment' => 'nullable|string',
            'likert_answers' => 'required|array',
            'likert_answers.*' => 'required|integer|min:1|max:4', // 1-4 sesuai opsi Likert
        ]);

        try {
            DB::transaction(function () use ($validated, $course, $user) {
                // 1. Simpan ulasan untuk kursus
                $course->reviews()->updateOrCreate(
                    ['user_id' => $user->id], // Kunci pencarian
                    [ // Data untuk update atau create
                        'rating' => $validated['course_rating'],
                        'comment' => $validated['course_comment'],
                    ]
                );

                // 2. Simpan ulasan untuk instruktur
                $course->instructorReviews()->updateOrCreate(
                    ['user_id' => $user->id, 'instructor_id' => $course->instructor_id],
                    [
                        'rating' => $validated['instructor_rating'],
                        'comment' => $validated['instructor_comment'],
                    ]
                );

                // 3. Simpan jawaban skala Likert
                foreach ($validated['likert_answers'] as $questionId => $answer) {
                    $user->likertAnswers()->updateOrCreate(
                        ['course_id' => $course->id, 'likert_question_id' => $questionId],
                        ['answer' => $answer]
                    );
                }
            });
        } catch (\Exception $e) {
            // Log error untuk catatan permanen
            \Log::error('Review Store Error: ' . $e->getMessage() . ' on line ' . $e->getLine());
            // Jika terjadi error, kembalikan respons error
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan ulasan.'], 500);
        }

        // Jika berhasil, kembalikan respons sukses
        return response()->json(['success' => true, 'message' => 'Terima kasih atas ulasan Anda!']);
    }
}
