<?php

namespace App\Http\Controllers;

use App\Models\PlatformReview;

class TestimonialController extends Controller
{
    /**
     * Menampilkan halaman semua testimoni platform.
     */
    public function index()
    {
        $allPlatformReviews = PlatformReview::with('user.studentProfile')
            ->whereNotNull('comment')
            ->latest()
            ->simplePaginate(6); // Tampilkan 6 per halaman

        return view('testimonials', compact('allPlatformReviews'));
    }
}
