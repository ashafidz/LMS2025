<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    // index
    public function index()
    {
        $user = Auth::user();
        $profile = null; // Default to null

        // Get the active role from the session
        $activeRole = session('active_role');


        // Load the profile only if the role requires it
        if ($activeRole === "student" && $user->studentProfile) {
            $profile = $user->studentProfile;
        } elseif ($activeRole === "instructor" && $user->instructorProfile) {
            $profile = $user->instructorProfile;
        }

        // For admins/superadmins, $profile will correctly remain null.
        // return for student and instructor\
        if ($activeRole === "student" || $activeRole === "instructor") {
            return view("profile.profile-student_instructor", compact("user", "profile"));
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin')) {
            return view("profile.profile-admin_superadmin", compact("user", "profile"));
        }
    }
    /**
     * Show the form for editing the user's profile.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        $profile = null; // Default to null

        // Get the active role from the session
        $activeRole = $request->session()->get("active_role");

        // Load the profile only if the role requires it
        if ($activeRole === "student" && $user->studentProfile) {
            $profile = $user->studentProfile;
        } elseif ($activeRole === "instructor" && $user->instructorProfile) {
            $profile = $user->instructorProfile;
        }

        // Ambil semua badge yang sudah dimiliki pengguna
        $unlockedBadges = $user->badges()->get();


        // For admins/superadmins, $profile will correctly remain null.

        // return for student and instructor\
        if ($activeRole === "student" || $activeRole === "instructor") {
            return view("profile.edit-student_instructor", compact("user", "profile", 'unlockedBadges'));
        } elseif (Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin')) {
            return view("profile.edit-admin_superadmin", compact("user", "profile"));
        }
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $activeRole = session('active_role');

        // --- VALIDATION ---
        $validationRules = [
            // User fields
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'gender' => ['nullable', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB Max
            'equipped_badge_id' => 'nullable|exists:badges,id',
            'unique_id_number' => 'nullable|string|max:255',
        ];

        // Add profile-specific validation if the user is a student or instructor
        if ($activeRole === 'student' || $activeRole === 'instructor') {
            $validationRules = array_merge($validationRules, [
                'headline' => ['nullable', 'string', 'max:255'],
                'bio' => ['nullable', 'string'],
                'website_url' => ['nullable', 'url'],
                'highest_level_of_education' => ['nullable', 'string', 'max:255'],
                'profession' => ['nullable', 'string', 'max:255'],
                'company_or_institution_name' => ['nullable', 'string', 'max:255'],
                'company_address' => ['nullable', 'string'],
                'company_tax_id' => ['nullable', 'string', 'max:255'],
                'unique_id_number' => 'nullable|string|max:255',
            ]);
        }

        $validated = $request->validate($validationRules);

        // Pastikan pengguna hanya bisa memasang badge yang mereka miliki
        if ($request->filled('equipped_badge_id')) {
            if (!$user->badges()->where('badge_id', $request->equipped_badge_id)->exists()) {
                return back()->withErrors(['equipped_badge_id' => 'Anda tidak bisa memasang badge yang tidak Anda miliki.']);
            }
        }

        // --- UPDATE USER ---
        $userData = [
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'address' => $validated['address'],
            'equipped_badge_id' => $validated['equipped_badge_id'] ?? null,
            'unique_id_number' => $validated['unique_id_number']
        ];

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old picture if it exists
            if ($user->profile_picture_url) {
                Storage::disk('public')->delete($user->profile_picture_url);
            }
            // Store new picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $userData['profile_picture_url'] = $path;
        }

        $user->update($userData);

        // --- UPDATE PROFILE (for students/instructors) ---
        if ($activeRole === 'student' || $activeRole === 'instructor') {
            $profile = $activeRole === 'student' ? $user->studentProfile : $user->instructorProfile;

            if ($profile) {
                $profile->update([
                    'headline' => $validated['headline'],
                    'bio' => $validated['bio'],
                    'website_url' => $validated['website_url'],
                    'highest_level_of_education' => $validated['highest_level_of_education'],
                    'profession' => $validated['profession'],
                    'company_or_institution_name' => $validated['company_or_institution_name'],
                    'company_address' => $validated['company_address'],
                    'company_tax_id' => $validated['company_tax_id'],
                    'unique_id_number' => $validated['unique_id_number']
                ]);
            }
        }
        return redirect()->route('user.profile.index')->with('status', 'Profile updated successfully!');
    }
}
