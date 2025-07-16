<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    /**
     * Menampilkan daftar kursus yang menunggu review.
     */
    public function index()
    {
        $pendingCourses = Course::where('status', 'pending_review')
            ->with('instructor', 'category')
            ->latest()
            ->paginate(15);

        return view('shared-admin.publication.index', compact('pendingCourses'));
    }

    /**
     * Mempublikasikan kursus dan menetapkan harganya.
     */
    public function publish(Request $request, Course $course)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        // Pastikan kursus yang akan di-publish memang sedang dalam status review
        if ($course->status !== 'pending_review') {
            return back()->with('error', 'Kursus ini tidak bisa dipublikasikan.');
        }

        $course->price = $validated['price'];
        $course->status = 'published';
        $course->save();

        // Di sini Anda bisa menambahkan logika untuk mengirim notifikasi ke instruktur
        // bahwa kursusnya telah disetujui.

        return back()->with('success', 'Kursus berhasil dipublikasikan.');
    }

    /**
     * Menolak kursus yang direview.
     */
    public function reject(Request $request, Course $course)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($course->status !== 'pending_review') {
            return back()->with('error', 'Aksi tidak valid untuk kursus ini.');
        }

        $course->status = 'rejected';
        $course->save();

        // Di sini Anda bisa menambahkan logika untuk menyimpan alasan penolakan
        // dan mengirim notifikasi ke instruktur.

        return back()->with('success', 'Kursus telah ditolak.');
    }
}
