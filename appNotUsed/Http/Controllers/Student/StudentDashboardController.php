<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    /**
     * Menampilkan halaman "Kursus Saya"
     */
    public function myCourses()
    {
        $user = Auth::user();

        // Ambil semua kursus di mana pengguna terdaftar
        $enrolledCourses = $user->enrollments()
            ->with('instructor', 'category')
            // Menghitung total pelajaran di setiap kursus
            ->withCount('lessons')
            // Menghitung pelajaran yang sudah diselesaikan oleh pengguna ini
            ->withCount(['lessons as completed_lessons_count' => function ($query) use ($user) {
                $query->whereHas('completedBy', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id);
                });
            }])
            ->latest('course_enrollments.created_at')
            ->paginate(9);

        return view('student.courses.index', compact('enrolledCourses'));
    }
}
