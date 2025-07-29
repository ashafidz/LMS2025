<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LessonDiscussion;

class LessonDiscussionController extends Controller
{
    /**
     * Menyimpan komentar atau balasan baru ke database.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $user = Auth::user();

        // Validasi: Pastikan pengguna terdaftar di kursus ini
        $is_enrolled = $user->enrollments()->where('course_id', $lesson->module->course_id)->exists();
        if (!$is_enrolled) {
            return back()->with('error', 'Anda harus terdaftar di kursus ini untuk berdiskusi.');
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:lesson_discussions,id', // Untuk balasan
        ]);

        // Buat diskusi/komentar baru
        $lesson->discussions()->create([
            'user_id' => $user->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return back()->with('success', 'Komentar Anda berhasil dikirim.');
    }



    /**
     * "Menghapus" sebuah komentar dengan mengganti isinya.
     */
    public function destroy(LessonDiscussion $discussion)
    {
        // Otorisasi: Pastikan hanya pemilik komentar yang bisa menghapus
        // if ($discussion->user_id !== Auth::id()) {
        //     abort(403, 'Anda tidak memiliki izin untuk menghapus komentar ini.');
        // }

        // Jangan hapus record, cukup update konten dan statusnya
        $discussion->update([
            'content' => '[Komentar ini telah dihapus oleh pengguna]',
            'is_deleted' => true,
        ]);

        return back()->with('success', 'Komentar berhasil dihapus.');
    }
}
