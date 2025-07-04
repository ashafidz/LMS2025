<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
});

// Route::get('/instructor/pending', function () {
//     // If user is not an instructor or is approved, redirect them away
//     if (Auth::user()->role !== 'instructor' || Auth::user()->instructorProfile?->application_status !== 'pending') {
//         return redirect('/dashboard'); // or to the instructor panel if you have a separate dashboard route
//     }
//     return view('auth.pending-approval');
// })->middleware(['auth', 'verified'])->name('instructor.pending');

Route::get('/instructor/pending', function () {
    // If user is not a pending instructor, send them away.
    if (!Auth::user()->hasRole('instructor') || Auth::user()->instructorProfile?->application_status !== 'pending') {
        return redirect('/dashboard');
    }
    // Show the new pending view
    return view('auth.pending-instructor');
})->middleware(['auth', 'verified'])->name('instructor.pending');
