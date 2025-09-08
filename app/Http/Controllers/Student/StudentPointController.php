<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPointController extends Controller
{
    /**
     * Menampilkan halaman "Kelola Poin" milik pengguna.
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil riwayat poin, urutkan dari yang terbaru, dan paginasi
        $pointHistories = $user->pointHistories()
            ->latest()
            ->simplePaginate(10);

        return view('student.my-points.index', compact('user', 'pointHistories'));
    }
}
