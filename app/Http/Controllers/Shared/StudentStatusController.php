<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentStatusController extends Controller
{
    /**
     * Display a listing of the Student Status.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        // Fetch student profiles with their user, optionally filtered by name, email or ID number
        $students_status_data = StudentProfile::with('user')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where(function ($query) use ($like) {
                    $query->where('unique_id_number', 'like', $like)
                        ->orWhereHas('user', function ($query) use ($like) {
                            $query->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

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
