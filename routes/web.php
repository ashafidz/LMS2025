<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\Shared\InstructorApplicationController;

Route::get('/switch-role/{role}', [RoleSwitchController::class, 'switch'])->name('role.switch');

Route::get('/', function () {
    return view('home');
})->name('home');
Route::get('/courses', function () {
    return view('catalog');
})->name('courses');
Route::get('/about', function () {
    return view('about');
})->name('about');

// * group route for superadmin
Route::middleware(['auth', 'verified', 'role:superadmin'])->group(function () {
    Route::view('/superadmin/dashboard', 'superadmin.dashboard')->name('superadmin.dashboard');

    Route::get('/superadmin/instructor-application', [InstructorApplicationController::class, 'index'])->name('superadmin.instructor-application.index');
    Route::patch('/superadmin/applications/{application}/approve', [InstructorApplicationController::class, 'approve'])->name('superadmin.instructor-applications.approve');
    Route::patch('/superadmin/applications/{application}/reject', [InstructorApplicationController::class, 'reject'])->name('superadmin.instructor-applications.reject');
});

// * group route for admin
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::get('/admin/instructor-application', [InstructorApplicationController::class, 'index'])->name('admin.instructor-application.index');
    Route::patch('/admin/applications/{application}/approve', [InstructorApplicationController::class, 'approve'])->name('admin.instructor-applications.approve');
    Route::patch('/admin/applications/{application}/reject', [InstructorApplicationController::class, 'reject'])->name('admin.instructor-applications.reject');
});

// * group route for instructor
Route::middleware(['auth', 'verified', 'role:instructor'])->group(function () {
    Route::view('/instructor/dashboard', 'instructor.dashboard')->name('instructor.dashboard');

    Route::get('/instructor/pending', function () {
        // If user is not a pending instructor, send them away.
        if (!Auth::user()->hasRole('instructor') || Auth::user()->instructorProfile?->application_status !== 'pending') {
            return redirect()->route('instructor.dashboard');
        }
        // Show the new pending view
        return view('auth.pending-instructor');
    })->name('instructor.pending');
});

// * group route for student
Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::view('/student/dashboard', 'student.dashboard')->name('student.dashboard');
});
