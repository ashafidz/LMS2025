<?php

namespace App\Http\Controllers\Shared;

use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CourseEnrollmentController extends Controller
{
    /**
     * Menampilkan halaman daftar kursus untuk manajemen pengguna.
     */
    public function index()
    {
        $courses = Course::with('instructor')->latest()->paginate(15);
        return view('shared-admin.course-enrollments.index', compact('courses'));
    }

    /**
     * Menampilkan daftar pengguna yang terdaftar di kursus tertentu.
     */
    public function show(Course $course)
    {
        $enrolledUsers = $course->students()->paginate(20);
        return view('shared-admin.course-enrollments.show', compact('course', 'enrolledUsers'));
    }

    /**
     * Menampilkan halaman untuk mencari dan menambahkan pengguna baru ke kursus.
     */
    public function create(Request $request, Course $course)
    {
        // Ambil ID semua pengguna yang SUDAH terdaftar di kursus ini
        $enrolledUserIds = $course->students()->pluck('users.id');

        // Query untuk mencari pengguna dengan role 'student' yang BELUM terdaftar
        $query = User::role('student')->whereNotIn('id', $enrolledUserIds);

        // Terapkan filter pencarian jika ada
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $availableUsers = $query->paginate(15);

        return view('shared-admin.course-enrollments.create', compact('course', 'availableUsers'));
    }

    /**
     * Menyimpan (mendaftarkan) pengguna baru ke dalam kursus.
     */
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Gunakan syncWithoutDetaching untuk menambahkan tanpa menghapus yang lama
        $course->students()->syncWithoutDetaching($validated['user_ids']);

        return redirect()->route(Auth::user()->getRoleNames()->first() . '.course-enrollments.show', $course)
            ->with('success', count($validated['user_ids']) . ' pengguna berhasil ditambahkan.');
    }

    /**
     * Menghapus satu atau lebih pengguna dari kursus.
     */
    public function destroy(Request $request, Course $course)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Gunakan detach untuk menghapus pendaftaran
        $course->students()->detach($validated['user_ids']);

        return back()->with('success', count($validated['user_ids']) . ' pengguna berhasil dihapus dari kursus.');
    }
}
