<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    /**
     * Menampilkan halaman profil publik untuk pengguna tertentu.
     */
    public function show(User $user)
    {
        // Ambil profil yang relevan (student atau instructor)
        $profile = $user->hasRole('instructor') ? $user->instructorProfile : $user->studentProfile;

        // Inisialisasi variabel
        $publishedCourses = collect();
        $totalStudents = 0;

        // Jika pengguna adalah instruktur, ambil data kursus dan total siswa
        if ($user->hasRole('instructor')) {
            $publishedCourses = $user->courses()
                ->where('status', 'published')
                ->with('instructor', 'category')
                ->get();

            // Hitung total siswa dari semua kursus yang dipublikasikan
            $totalStudents = $publishedCourses->reduce(function ($carry, $course) {
                return $carry + $course->students()->count();
            }, 0);
        }

        // Ambil total poin yang pernah didapat dari semua kursus
        $totalPoints = $user->coursePoints()->sum('points_earned');

        return view('profile.show', compact(
            'user',
            'profile',
            'publishedCourses',
            'totalStudents',
            'totalPoints'
        ));
    }
}
