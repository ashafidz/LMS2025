<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentCertificateController extends Controller
{
    /**
     * Menampilkan halaman daftar sertifikat milik pengguna.
     */
    public function index()
    {
        $certificates = Auth::user()->certificates()
            ->with('course') // Eager load untuk efisiensi
            ->latest('issued_at') // Urutkan dari yang terbaru
            ->simplePaginate(10);

        return view('student.certificates.index', compact('certificates'));
    }
}
