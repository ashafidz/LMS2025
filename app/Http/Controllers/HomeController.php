<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\PlatformReview;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman utama dengan data yang diperlukan.
     */
    public function index()
    {
        // Ambil 3 kursus terpopuler
        $popularCourses = Course::where('status', 'published')
            ->withCount('students', 'lessons')
            ->orderBy('students_count', 'desc')
            ->take(3)
            ->get();

        // Pisahkan menjadi tiga variabel berbeda
        $mostPopularCourse = $popularCourses->get(0); // Kursus paling populer (urutan 1)
        $secondMostPopularCourse = $popularCourses->get(1); // Urutan 2
        $thirdMostPopularCourse = $popularCourses->get(2); // Urutan 3


        // 2. Ambil 4 ulasan platform terbaru dengan rating tinggi
        $platformReviews = PlatformReview::with('user.studentProfile')
            ->whereNotNull('comment') // Hanya ambil yang ada komentarnya
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return view('home', compact(
            'mostPopularCourse',
            'secondMostPopularCourse',
            'thirdMostPopularCourse',
            'platformReviews' // 3. Kirim data ke view
        ));
    }
}
