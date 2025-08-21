<?php

namespace App\Http\Controllers;

use App\Models\PlatformReview;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    //
    public function index()
    {
        // 2. Ambil 4 ulasan platform terbaru dengan rating tinggi
        $platformReviews = PlatformReview::with('user.studentProfile')
            ->whereNotNull('comment') // Hanya ambil yang ada komentarnya
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return view('about', compact('platformReviews'));
    }
}
