<?php

namespace App\Http\Controllers\Shared;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Mail\InstructorApproved;
use App\Mail\InstructorRejected;
use App\Models\InstructorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;

class InstructorApplicationController extends Controller
{
    /**
     * Menampilkan form pendaftaran instruktur.
     */
    public function create()
    {
        return view('student.apply-instructor');
    }

    /**
     * Menyimpan data pendaftaran instruktur.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi data yang masuk
        $validated = $request->validate([
            'headline' => 'required|string|max:255',
            'bio' => 'required|string',
            'website_url' => 'nullable|url',
            'unique_id_number' => 'nullable|string|max:255',
        ]);

        // Buat atau perbarui profil instruktur dengan status 'pending'
        $user->instructorProfile()->updateOrCreate(
            ['user_id' => $user->id], // Kunci untuk mencari
            [ // Data untuk diisi atau diperbarui
                'headline' => $validated['headline'],
                'bio' => $validated['bio'],
                'website_url' => $validated['website_url'],
                'unique_id_number' => $validated['unique_id_number'],
                'application_status' => 'pending',
            ]
        );

        $user->assignRole('instructor');

        // Arahkan ke halaman "pending"
        return redirect()->route('instructor.pending')->with('success', 'Pendaftaran Anda berhasil dikirim dan sedang ditinjau.');
    }
    /**
     * Display a listing of the instructor applications.
     */
    public function index(): View
    {
        // Fetch all instructor profiles and load their associated user data
        $applications = InstructorProfile::with('user')->latest()->simplePaginate(10);

        return view('shared-admin.manajemen-user.instructor-application.index', compact('applications'));
    }

    /**
     * Approve the instructor application.
     */
    public function approve(InstructorProfile $application): RedirectResponse
    {
        // Update the status to 'approved'
        $application->update(['application_status' => 'approved']);

        // Send the approval email
        Mail::to($application->user->email)->send(new InstructorApproved($application->user));

        return back()->with('success', 'Instructor application has been approved.');
    }

    /**
     * Reject the instructor application.
     */
    public function reject(InstructorProfile $application): RedirectResponse
    {
        // Update the status to 'rejected'
        $application->update(['application_status' => 'rejected']);

        // Send the rejection email
        Mail::to($application->user->email)->send(new InstructorRejected($application->user));

        return back()->with('success', 'Instructor application has been rejected.');
    }

    /**
     *  Deactivate the instructor.
     */
    public function deactive(InstructorProfile $application): RedirectResponse
    {

        // Update the status to 'deactivate'
        $application->update(['application_status' => 'deactive']);

        return back()->with('success', 'Instructor has been deactivated.');
    }

    /**
     * Reactivate the instructor.
     */
    public function reactivate(InstructorProfile $application): RedirectResponse
    {
        // Update the status back to 'approved'
        $application->update(['application_status' => 'approved']);

        // Optionally, you could send a notification email here.

        return back()->with('success', 'Instructor has been reactivated.');
    }
}
