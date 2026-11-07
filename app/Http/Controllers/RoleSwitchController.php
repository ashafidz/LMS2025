<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    public function switch($role)
    {
        $user = Auth::user();

        abort_unless(in_array($role, ['superadmin', 'admin', 'instructor', 'student'], true), 403);

        // Special handling for instructor role:
        // If a student user tries to switch to instructor, allow them to reach the application/status pages
        // but DO NOT store 'active_role' = 'instructor' in their session.
        if ($role === 'instructor' && ! $user->hasRole('instructor')) {
            $profile = $user->instructorProfile;
            if ($profile) {
                $status = $profile->application_status;
                if ($status === 'pending') {
                    return redirect()->route('instructor.pending');
                }
                if ($status === 'rejected') {
                    return redirect()->route('instructor.rejected');
                }
                if ($status === 'deactive') {
                    return redirect()->route('instructor.deactive');
                }
            }

            return redirect()->route('student.apply_instructor.create');
        }

        // For all actual role switches, user must possess the role
        abort_unless($user->hasRole($role), 403);

        // Simpan peran yang dipilih ke dalam session
        session(['active_role' => $role]);

        if ($role === 'instructor') {
            $status = $user->instructorProfile?->application_status;
            if ($status === 'approved') {
                return $this->switched('instructor.dashboard', 'Instruktur');
            } elseif ($status === 'pending') {
                return redirect()->route('instructor.pending');
            } elseif ($status === 'rejected') {
                return redirect()->route('instructor.rejected');
            } elseif ($status === 'deactive') {
                return redirect()->route('instructor.deactive');
            }
        }

        if ($role === 'student') {
            return $this->switched('student.dashboard', 'Siswa');
        }
        if ($role === 'admin') {
            return $this->switched('admin.dashboard', 'Admin');
        }
        if ($role === 'superadmin') {
            return $this->switched('superadmin.dashboard', 'Superadmin');
        }

        // Default fallback
        return redirect('/home');
    }

    /** Redirect to the new role's dashboard with a success toast (rendered by the layout, see rs-dialog.js). */
    private function switched(string $route, string $label)
    {
        session()->flash('rs_toast', 'Berhasil beralih ke mode '.$label.'.');

        return redirect()->route($route);
    }
}
