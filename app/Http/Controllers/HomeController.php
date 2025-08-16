<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

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

        return view('home', compact(
            'mostPopularCourse',
            'secondMostPopularCourse',
            'thirdMostPopularCourse'
        ));
    }
}
