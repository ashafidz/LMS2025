<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    //
    /**
     * Switch the user's active role in the session.
     */
    public function switch(Request $request, $role)
    {
        // 1. Get the authenticated user
        $user = Auth::user();

        // 2. Define the roles the user is allowed to switch between
        $switchableRoles = [];
        if ($user->hasRole('student')) {
            $switchableRoles[] = 'student';
        }
        if ($user->hasRole('instructor') && $user->instructorProfile?->application_status === 'approved') {
            $switchableRoles[] = 'instructor';
        }
        // Add other roles like 'admin' if they should be switchable
        // if ($user->hasRole('admin')) {
        //     $switchableRoles[] = 'admin';
        // }


        // 3. Validate that the requested role is one the user actually has
        // and is allowed to switch to. This is a crucial security step!
        if (!in_array($role, $switchableRoles)) {
            // If they try to switch to a role they don't have, abort.
            abort(403, 'You do not have permission to switch to this role.');
        }

        // 4. Update the session with the new active role
        session(['active_role' => $role]);

        // 5. Redirect the user to their new dashboard
        if ($role === 'instructor') {
            return redirect()->route('instructor.dashboard'); // Or wherever instructors should land
        }

        // Default redirect for students
        return redirect()->route('student.dashboard'); // Or wherever students should land
    }
}
