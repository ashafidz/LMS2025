<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Total Instruktur (users dengan role instructor atau yang punya course)
        $totalInstructors = User::whereHas('courses')->count();

        // 2. Total Kursus
        $totalCourses = Course::count();

        // 3. Total Students di semua kursus (unique students)
        $totalStudentsAllCourses = DB::table('course_enrollments')
            ->distinct()
            ->count('user_id');

        // 4. Total Enrollments (bisa sama student tapi beda course)
        $totalEnrollments = DB::table('course_enrollments')->count();

        // 5. Data Instruktur dengan jumlah kursus mereka
        $instructorsWithCourses = User::select('users.id', 'users.name')
            ->withCount('courses')
            ->whereHas('courses')
            ->orderByDesc('courses_count')
            // ->limit(10)
            ->get();

        // 6. Kategori dengan jumlah total students
        $categoriesWithStudents = CourseCategory::select('course_categories.*')
            ->withCount([
                'courses as total_students' => function ($query) {
                    $query->join('course_enrollments', 'courses.id', '=', 'course_enrollments.course_id');
                }
            ])
            ->orderByDesc('total_students')
            // ->limit(10)
            ->get();

        // 7. Statistik pertumbuhan (contoh sederhana - bisa dikembangkan)
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        $currentMonthInstructors = User::whereHas('courses')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthInstructors = User::whereHas('courses')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $instructorGrowth = $lastMonthInstructors > 0
            ? round((($currentMonthInstructors - $lastMonthInstructors) / $lastMonthInstructors) * 100, 1)
            : 0;

        // Pertumbuhan kursus baru bulan ini
        $newCoursesThisMonth = Course::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Pertumbuhan enrollment
        $currentMonthEnrollments = DB::table('course_enrollments')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthEnrollments = DB::table('course_enrollments')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $enrollmentGrowth = $lastMonthEnrollments > 0
            ? round((($currentMonthEnrollments - $lastMonthEnrollments) / $lastMonthEnrollments) * 100, 1)
            : 0;

        return view('admin.dashboard', compact(
            'totalInstructors',
            'totalCourses',
            'totalStudentsAllCourses',
            'totalEnrollments',
            'instructorsWithCourses',
            'categoriesWithStudents',
            'instructorGrowth',
            'newCoursesThisMonth',
            'enrollmentGrowth'
        ));
    }

    public function searchInstructors(Request $request)
    {
        $search = $request->get('search', '');

        $instructors = User::select('users.id', 'users.name')
            ->withCount('courses')
            ->whereHas('courses')
            ->when($search, function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%");
            })
            ->orderByDesc('courses_count')
            // ->limit(10)
            ->get();

        return response()->json([
            'data' => $instructors->map(function ($instructor) {
                return [
                    'name' => $instructor->name,
                    'courses_count' => $instructor->courses_count
                ];
            })
        ]);
    }

    public function searchCategories(Request $request)
    {
        $search = $request->get('search', '');

        $categories = CourseCategory::select('course_categories.*')
            ->withCount([
                'courses as total_students' => function ($query) {
                    $query->join('course_enrollments', 'courses.id', '=', 'course_enrollments.course_id');
                }
            ])
            ->when($search, function ($query) use ($search) {
                $query->where('course_categories.name', 'like', "%{$search}%");
            })
            ->orderByDesc('total_students')
            // ->limit(10)
            ->get();

        return response()->json([
            'data' => $categories->map(function ($category) {
                return [
                    'name' => $category->name,
                    'total_students' => $category->total_students
                ];
            })
        ]);
    }
}
