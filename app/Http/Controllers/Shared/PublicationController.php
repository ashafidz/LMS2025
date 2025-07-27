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
     * Mempublikasikan kursus dan menetapkan harganya (baik uang maupun poin).
     */
    public function publish(Request $request, Course $course)
    {
        // Pastikan kursus yang akan di-publish memang sedang dalam status review
        if ($course->status !== 'pending_review') {
            return back()->with('error', 'Kursus ini tidak bisa dipublikasikan.');
        }

        // Validasi dinamis berdasarkan tipe pembayaran kursus
        if ($course->payment_type === 'money') {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
            ]);
            $course->price = $validated['price'];
            $course->points_price = 0; // Reset harga poin
        } elseif ($course->payment_type === 'points') {
            $validated = $request->validate([
                'points_price' => 'required|integer|min:0',
            ]);
            $course->points_price = $validated['points_price'];
            $course->price = 0; // Reset harga uang
        }

        $course->status = 'published';
        $course->save();

        // Di sini Anda bisa menambahkan logika untuk mengirim notifikasi ke instruktur

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
