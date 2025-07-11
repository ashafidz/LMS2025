<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;
use App\Models\StudentProfile;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentStatusController extends Controller
{
    /**
     * Display a listing of the Student Status.
     */
    public function index(): View
    {
        // Fetch all student profiles and load their associated user data
        $students_status_data = StudentProfile::with('user')->latest()->paginate(10);

        return view('shared-admin.manajemen-user.manajemen-student.index', compact('students_status_data'));
    }


    /**
     *  Deactivate the student.
     */
    public function deactive(StudentProfile $student_status_data): RedirectResponse
    {

        // Update the status to 'deactivate'
        $student_status_data->update(['student_status' => 'deactive']);

        return back()->with('success', 'Student telah di deaktifasi');
    }

    /**
     * Reactivate the student.
     */
    public function reactivate(StudentProfile $student_status_data): RedirectResponse
    {
        // Update the status back to 'approved'
        $student_status_data->update(['student_status' => 'active']);

        // Optionally, you could send a notification email here.

        return back()->with('success', 'Student telah di aktifasi kembali');
    }
}
