<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\LessonController;
use App\Http\Controllers\Instructor\ModuleController;
use App\Http\Controllers\Shared\PublicationController;
use App\Http\Controllers\Instructor\QuestionController;
use App\Http\Controllers\Student\StudentQuizController;


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

use App\Http\Controllers\Shared\StudentStatusController;
use App\Http\Controllers\Instructor\QuizQuestionController;
use App\Http\Controllers\Instructor\QuestionTopicController;
use App\Http\Controllers\Shared\InstructorApplicationController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;

Route::middleware(['auth'])->group(function () {
    // profile index
    Route::get('/user/profile/view', [UserProfileController::class, 'index'])->name('user.profile.index');
    Route::get('/user/profile/edit', [UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::get('/user/password', function () {
        return view('auth.password');
    })->name('user.password.edit');

    Route::get('/courses/{course:slug}', [StudentCourseController::class, 'show'])->name('student.courses.show');
    // Rute baru untuk mengambil konten pelajaran secara dinamis (AJAX)
    Route::get('/lessons/{lesson}/content', [StudentCourseController::class, 'getContent'])->name('student.lessons.content')->middleware('auth');

    // Halaman perkenalan sebelum memulai kuis
    Route::get('/quiz/{quiz}/start', [StudentQuizController::class, 'start'])->name('student.quiz.start');

    // Aksi untuk memulai kuis dan membuat 'attempt'
    Route::post('/quiz/{quiz}/begin', [StudentQuizController::class, 'begin'])->name('student.quiz.begin');

    // Halaman utama pengerjaan kuis
    Route::get('/quiz/attempt/{attempt}', [StudentQuizController::class, 'take'])->name('student.quiz.take');

    // Aksi untuk mengirimkan semua jawaban
    Route::post('/quiz/attempt/{attempt}/submit', [StudentQuizController::class, 'submit'])->name('student.quiz.submit');

    // Halaman untuk menampilkan hasil akhir kuis
    Route::get('/quiz/attempt/{attempt}/result', [StudentQuizController::class, 'result'])->name('student.quiz.result');

    // RUTE BARU UNTUK MENGECEK JAWABAN SECARA REAL-TIME (AJAX)
    Route::post('/quiz/check-answer', [StudentQuizController::class, 'checkAnswerAjax'])->name('student.quiz.check_answer');
});

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

    Route::get('/superadmin/publication', [PublicationController::class, 'index'])->name('superadmin.publication.index');
    Route::patch('/superadmin/publication/{course}/publish', [PublicationController::class, 'publish'])->name('superadmin.publication.publish');
    Route::patch('/superadmin/publication/{course}/reject', [PublicationController::class, 'reject'])->name('superadmin.publication.reject');
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

    Route::get('/admin/publication', [PublicationController::class, 'index'])->name('admin.publication.index');
    Route::patch('/admin/publication/{course}/publish', [PublicationController::class, 'publish'])->name('admin.publication.publish');
    Route::patch('/admin/publication/{course}/reject', [PublicationController::class, 'reject'])->name('admin.publication.reject');
});



// Group route for instructor
Route::middleware(['auth', 'verified', 'role:instructor'])->group(function () {

    // Status-specific instructor routes
    Route::view('/instructor/pending', 'auth.pending-instructor')->name('instructor.pending');
    Route::view('/instructor/deactive', 'auth.deactive-instructor')->name('instructor.deactive');
    Route::view('/instructor/rejected', 'auth.rejected-instructor')->name('instructor.rejected');

    // Routes for APPROVED instructors
    Route::middleware(['checkInstructorStatus'])->group(function () {

        Route::view('/instructor/dashboard', 'instructor.dashboard')->name('instructor.dashboard');

        // Explicitly defined routes for Question Topics (as per user request)
        Route::get('/instructor/question-topics', [QuestionTopicController::class, 'index'])->name('instructor.question-bank.topics.index');
        Route::get('/instructor/question-topics/create', [QuestionTopicController::class, 'create'])->name('instructor.question-bank.topics.create');
        Route::post('/instructor/question-topics', [QuestionTopicController::class, 'store'])->name('instructor.question-bank.topics.store');
        Route::get('/instructor/question-topics/{topic}', [QuestionTopicController::class, 'show'])->name('instructor.question-bank.topics.show');
        Route::get('/instructor/question-topics/{topic}/edit', [QuestionTopicController::class, 'edit'])->name('instructor.question-bank.topics.edit');
        Route::put('/instructor/question-topics/{topic}', [QuestionTopicController::class, 'update'])->name('instructor.question-bank.topics.update');
        Route::patch('/instructor/question-topics/{topic}', [QuestionTopicController::class, 'update']); // Patch route for update
        Route::delete('/instructor/question-topics/{topic}', [QuestionTopicController::class, 'destroy'])->name('instructor.question-bank.topics.destroy');

        // Custom route for cloning a question
        Route::post('questions/{question}/clone', [QuestionController::class, 'clone'])->name('instructor.question-bank.questions.clone');

        // Explicitly defined routes for Questions (as per user request)
        // Nested routes for index, create, store
        Route::get('/instructor/question-topics/{topic}/questions', [QuestionController::class, 'index'])->name('instructor.question-bank.questions.index');
        Route::get('/instructor/question-topics/{topic}/questions/create', [QuestionController::class, 'create'])->name('instructor.question-bank.questions.create');
        Route::post('/instructor/question-topics/{topic}/questions', [QuestionController::class, 'store'])->name('instructor.question-bank.questions.store');

        // Shallow routes for show, edit, update, destroy
        Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('instructor.question-bank.questions.show');
        Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('instructor.question-bank.questions.edit');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('instructor.question-bank.questions.update');
        Route::patch('/questions/{question}', [QuestionController::class, 'update']); // Patch route for update
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('instructor.question-bank.questions.destroy');


        // GET /instructor/courses -> List all courses
        Route::get('/instructor/courses', [CourseController::class, 'index'])->name('instructor.courses.index');

        // GET /instructor/courses/create -> Show create form
        Route::get('/instructor/courses/create', [CourseController::class, 'create'])->name('instructor.courses.create');

        // POST /instructor/courses -> Store new course
        Route::post('/instructor/courses', [CourseController::class, 'store'])->name('instructor.courses.store');

        // GET /instructor/courses/{course} -> Show a single course
        Route::get('/instructor/courses/{course}', [CourseController::class, 'show'])->name('instructor.courses.show');

        // GET /instructor/courses/{course}/edit -> Show edit form
        Route::get('/instructor/courses/{course}/edit', [CourseController::class, 'edit'])->name('instructor.courses.edit');

        // PUT /instructor/courses/{course} -> Update a course
        Route::put('/instructor/courses/{course}', [CourseController::class, 'update'])->name('instructor.courses.update');

        // DELETE /instructor/courses/{course} -> Delete a course
        Route::delete('/instructor/courses/{course}', [CourseController::class, 'destroy'])->name('instructor.courses.destroy');



        // List all modules for a course & handle reordering
        Route::get('/instructor/courses/{course}/modules', [ModuleController::class, 'index'])->name('instructor.courses.modules.index');
        Route::post('/instructor/courses/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('instructor.courses.modules.reorder');

        // Create and Store a new module for a course
        Route::get('/instructor/courses/{course}/modules/create', [ModuleController::class, 'create'])->name('instructor.courses.modules.create');
        Route::post('/instructor/courses/{course}/modules', [ModuleController::class, 'store'])->name('instructor.courses.modules.store');

        // Edit, Update, and Destroy a specific module (shallow routes)
        Route::get('/instructor/modules/{module}/edit', [ModuleController::class, 'edit'])->name('instructor.modules.edit');
        Route::put('/instructor/modules/{module}', [ModuleController::class, 'update'])->name('instructor.modules.update');
        Route::delete('/instructor/modules/{module}', [ModuleController::class, 'destroy'])->name('instructor.modules.destroy');
    });


    // --- Routes untuk Mengelola Pelajaran (Lessons) dalam sebuah Modul ---

    // Menampilkan daftar pelajaran & menangani pengurutan
    Route::get('/instructor/modules/{module}/lessons', [LessonController::class, 'index'])->name('instructor.modules.lessons.index');
    Route::post('/instructor/modules/{module}/lessons/reorder', [LessonController::class, 'reorder'])->name('instructor.modules.lessons.reorder');

    // Membuat (Create) dan Menyimpan (Store) pelajaran baru
    Route::get('/instructor/modules/{module}/lessons/create', [LessonController::class, 'create'])->name('instructor.modules.lessons.create');
    Route::post('/instructor/modules/{module}/lessons', [LessonController::class, 'store'])->name('instructor.modules.lessons.store');

    // Mengedit (Edit), Memperbarui (Update), dan Menghapus (Destroy) pelajaran (rute dangkal/shallow)
    Route::get('/instructor/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('instructor.lessons.edit');
    Route::put('/instructor/lessons/{lesson}', [LessonController::class, 'update'])->name('instructor.lessons.update');
    Route::delete('/instructor/lessons/{lesson}', [LessonController::class, 'destroy'])->name('instructor.lessons.destroy');


    // --- Routes untuk Mengelola Soal di dalam sebuah Kuis ---

    // Halaman utama yang menampilkan daftar soal dalam kuis
    Route::get('/instructor/quizzes/{quiz}/manage-questions', [QuizQuestionController::class, 'index'])->name('instructor.quizzes.manage_questions');

    // Halaman untuk memilih soal dari Bank Soal
    Route::get('/instructor/quizzes/{quiz}/browse-bank', [QuizQuestionController::class, 'browseBank'])->name('instructor.quizzes.browse_bank');

    // Menyimpan soal yang dipilih dari Bank Soal ke dalam kuis
    Route::post('/instructor/quizzes/{quiz}/attach-questions', [QuizQuestionController::class, 'attachQuestions'])->name('instructor.quizzes.attach_questions');

    // Menghapus satu soal dari kuis
    Route::delete('/instructor/quizzes/{quiz}/detach-question/{question}', [QuizQuestionController::class, 'detachQuestion'])->name('instructor.quizzes.detach_question');

    Route::patch('/instructor/courses/{course}/submit-review', [CourseController::class, 'submitForReview'])->name('instructor.courses.submit_review');
    Route::patch('/instructor/courses/{course}/make-private', [CourseController::class, 'makePrivate'])->name('instructor.courses.make_private');
});

// // * group route for instructor
// Route::middleware(['auth', 'verified', 'role:instructor'])->group(function () {

//     Route::get('/instructor/pending', function () {
//         return view('auth.pending-instructor');
//     })->name('instructor.pending');

//     Route::get('/instructor/deactive', function () {
//         return view('auth.deactive-instructor');
//     })->name('instructor.deactive');

//     Route::get('/instructor/rejected', function () {
//         return view('auth.rejected-instructor');
//     })->name('instructor.rejected');

//     //! This nested group IS protected by the status-checking middleware.
//     Route::middleware(['checkInstructorStatus'])->group(function () {
//         Route::view('/instructor/dashboard', 'instructor.dashboard')->name('instructor.dashboard');
//         // Add any other instructor routes that require 'approved' status here.
//         // For example: Route::get('/instructor/my-courses', ...);


//         // Group for the Question Bank feature
//         Route::prefix('instructor.question-bank')->name('instructor.question-bank.')->group(function () {

//             // Resourceful route for managing Question Topics
//             // This will create routes for index, create, store, show, edit, update, destroy
//             Route::resource('topics', QuestionTopicController::class);

//             // Nested resourceful route for managing Questions within a Topic
//             // URL will be like: /instructor/question-bank/topics/{topic}/questions
//             Route::resource('topics.questions', QuestionController::class)->shallow();
//             // Using shallow() makes routes for specific questions simpler, e.g., /questions/{question}/edit
//             // instead of /topics/{topic}/questions/{question}/edit
//         });
//     });
// });




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
