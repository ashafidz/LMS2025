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
        $courses = $query->latest()->paginate(8)->withQueryString();

        // Ambil semua kategori untuk ditampilkan di sidebar filter
        $categories = CourseCategory::orderBy('name')->get();

        return view('catalog', compact('courses', 'categories'));
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

        $reviews = $course->reviews()->with('user')->latest()->paginate(5);
        $averageRating = $course->reviews()->avg('rating');
        $reviewCount = $course->reviews()->count();

        $is_enrolled = false;
        if (Auth::check()) {
            $is_enrolled = Auth::user()->enrollments()->where('course_id', $course->id)->exists();
        }

        return view('details-course', compact('course', 'is_enrolled', 'reviews', 'averageRating', 'reviewCount'));
    }
}
