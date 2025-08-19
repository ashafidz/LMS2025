<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    /**
     * Menampilkan halaman katalog kursus dengan filter dan pencarian.
     */
    public function index(Request $request)
    {
        // Mulai query dengan hanya mengambil kursus yang sudah 'published'
        $query = Course::where('status', 'published')
            ->with('instructor', 'category')
            // LOGIKA BARU: Hitung jumlah ulasan dan rata-rata rating secara efisien
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // 1. Logika untuk Pencarian
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        // 2. Logika untuk Filter Kategori
        if ($request->filled('categories')) {
            $query->whereIn('category_id', $request->input('categories'));
        }

        // Ambil hasil dengan urutan terbaru dan paginasi
        $courses = $query->latest()->simplePaginate(8)->withQueryString();

        // Ambil semua kategori untuk ditampilkan di sidebar filter
        $categories = CourseCategory::orderBy('name')->get();


        // LOGIKA BARU: Ambil 10 kursus terpopuler
        $popularCourses = Course::where('status', 'published')
            ->withCount('students')
            ->orderBy('students_count', 'desc')
            ->take(10)
            ->get();

        return view('catalog', compact('courses', 'categories', 'popularCourses'));
    }

    /**
     * Menampilkan halaman detail kursus untuk publik/penjualan.
     */
    public function show(Course $course)
    {
        if ($course->status !== 'published') {
            abort(404);
        }

        $course->load([
            'instructor',
            'category',
            'modules' => fn($q) => $q->orderBy('order'),
            'modules.lessons' => fn($q) => $q->orderBy('order')
        ]);

        $reviews = $course->reviews()->with('user')->latest()->simplePaginate(5);
        $averageRating = $course->reviews()->avg('rating');
        $reviewCount = $course->reviews()->count();

        $is_enrolled = false;
        if (Auth::check()) {
            $is_enrolled = Auth::user()->enrollments()->where('course_id', $course->id)->exists();
        }


        // LOGIKA BARU: Ambil kursus lain dalam kategori yang sama
        $relatedCourses = Course::where('status', 'published')
            ->where('category_id', $course->category_id)
            ->where('id', '!=', $course->id) // Kecualikan kursus saat ini
            ->inRandomOrder()
            ->take(10)
            ->get();

        return view('details-course', compact('course', 'is_enrolled', 'reviews', 'averageRating', 'reviewCount', 'relatedCourses'));
    }
}
