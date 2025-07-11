<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;
use App\Models\InstructorProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Mail\InstructorApproved;
use App\Mail\InstructorRejected;
use Illuminate\Support\Facades\Mail;

class InstructorApplicationController extends Controller
{
    /**
     * Display a listing of the instructor applications.
     */
    public function index(): View
    {
        // Fetch all instructor profiles and load their associated user data
        $applications = InstructorProfile::with('user')->latest()->paginate(10);

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
