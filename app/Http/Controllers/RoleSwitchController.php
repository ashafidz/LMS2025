<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    public function switch($role)
    {
        $user = Auth::user();

        // Simpan peran yang dipilih ke dalam session
        session(['active_role' => $role]);

        // LOGIKA BARU: Penanganan khusus untuk peran instruktur
        if ($role === 'instructor') {
            // Jika pengguna memang memiliki peran instruktur
            if ($user->hasRole('instructor')) {
                // Cek status pendaftarannya
                $status = $user->instructorProfile?->application_status;
                if ($status === 'approved') {
                    return redirect()->route('instructor.dashboard');
                } elseif ($status === 'pending') {
                    return redirect()->route('instructor.pending');
                } elseif ($status === 'rejected') {
                    return redirect()->route('instructor.rejected');
                } elseif ($status === 'deactive') {
                    return redirect()->route('instructor.deactive');
                }
            } else {
                // Jika pengguna adalah siswa yang mencoba menjadi instruktur
                $profile = $user->instructorProfile;
                if ($profile) {
                    // Jika sudah pernah mendaftar, arahkan ke halaman status
                    $status = $profile->application_status;
                    if ($status === 'pending') return redirect()->route('instructor.pending');
                    if ($status === 'rejected') return redirect()->route('instructor.rejected');
                    if ($status === 'deactive') return redirect()->route('instructor.deactive');
                }
                // Jika belum pernah mendaftar sama sekali, arahkan ke form pendaftaran
                return redirect()->route('student.apply_instructor.create');
            }
        }

        // Logika untuk peran lain (tetap sama)
        if ($role === 'student') {
            return redirect()->route('student.dashboard');
        }
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        // Default fallback
        return redirect('/home');
    }
}
