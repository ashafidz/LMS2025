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
            ->simplePaginate(15);

        return view('shared-admin.publication.index', compact('pendingCourses'));
    }

    /**
     * Mempublikasikan kursus dan menetapkan harganya (baik uang maupun poin).
     */
    public function publish(Request $request, Course $course)
    {
        if ($course->status !== 'pending_review') {
            return back()->with('error', 'Kursus ini tidak bisa dipublikasikan.');
        }

        // DIUBAH: Validasi dinamis berdasarkan tipe pembayaran kursus
        if ($course->payment_type === 'money') {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
            ]);
            $course->price = $validated['price'];
            $course->diamond_price = 0; // Reset harga diamond
        } elseif ($course->payment_type === 'diamonds') {
            $validated = $request->validate([
                'diamond_price' => 'required|integer|min:0', // Validasi untuk diamond_price
            ]);
            $course->diamond_price = $validated['diamond_price'];
            $course->price = 0; // Reset harga uang
        }

        $course->status = 'published';
        $course->save();

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
