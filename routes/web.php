<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\Shared\StudentStatusController;
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
    Route::patch('/superadmin/applications/{application}/deactive', [InstructorApplicationController::class, 'deactive'])->name('superadmin.instructor-applications.deactive');
    Route::patch('/superadmin/applications/{application}/reactivate', [InstructorApplicationController::class, 'reactivate'])->name('superadmin.instructor-applications.reactivate');

    Route::get('/superadmin/student-management', [StudentStatusController::class, 'index'])->name('superadmin.manajemen-student.index');
    Route::patch('/superadmin/student-management/{student_status_data}/deactive', [StudentStatusController::class, 'deactive'])->name('superadmin.manajemen-student.deactive');
    Route::patch('/superadmin/student-management/{student_status_data}/reactivate', [StudentStatusController::class, 'reactivate'])->name('superadmin.manajemen-student.reactivate');
});

// * group route for admin
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::get('/admin/instructor-application', [InstructorApplicationController::class, 'index'])->name('admin.instructor-application.index');
    Route::patch('/admin/applications/{application}/approve', [InstructorApplicationController::class, 'approve'])->name('admin.instructor-applications.approve');
    Route::patch('/admin/applications/{application}/reject', [InstructorApplicationController::class, 'reject'])->name('admin.instructor-applications.reject');
    Route::patch('/admin/applications/{application}/deactive', [InstructorApplicationController::class, 'deactive'])->name('admin.instructor-applications.deactive');
    Route::patch('/admin/applications/{application}/reactivate', [InstructorApplicationController::class, 'reactivate'])->name('admin.instructor-applications.reactivate');

    Route::get('/admin/student-management', [StudentStatusController::class, 'index'])->name('admin.manajemen-student.index');
    Route::patch('/admin/student-management/{student_status_data}/deactive', [StudentStatusController::class, 'deactive'])->name('admin.manajemen-student.deactive');
    Route::patch('/admin/student-management/{student_status_data}/reactivate', [StudentStatusController::class, 'reactivate'])->name('admin.manajemen-student.reactivate');
});

// * group route for instructor
Route::middleware(['auth', 'verified', 'role:instructor'])->group(function () {

    Route::get('/instructor/pending', function () {
        return view('auth.pending-instructor');
    })->name('instructor.pending');

    Route::get('/instructor/deactive', function () {
        return view('auth.deactive-instructor');
    })->name('instructor.deactive');

    Route::get('/instructor/rejected', function () {
        return view('auth.rejected-instructor');
    })->name('instructor.rejected');

    //! This nested group IS protected by the status-checking middleware.
    Route::middleware(['checkInstructorStatus'])->group(function () {
        Route::view('/instructor/dashboard', 'instructor.dashboard')->name('instructor.dashboard');
        // Add any other instructor routes that require 'approved' status here.
        // For example: Route::get('/instructor/my-courses', ...);
    });
});

// * group route for student
Route::middleware(['auth', 'verified', 'role:student'])->group(function () {

    Route::get('/student/deactive', function () {
        return view('auth.deactive-student');
    })->name('student.deactive');

    //! This nested group IS protected by the status-checking middleware.
    Route::middleware(['checkStudentStatus'])->group(function () {
        Route::view('/student/dashboard', 'student.dashboard')->name('student.dashboard');
    });
});
