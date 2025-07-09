<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // === COURSE STRUCTURE ===

        // Modules are the main sections or chapters of a course
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->integer('order')->comment('The order of the module within the course');
            $table->timestamps();
        });


        // The different types of lesson content (for the polymorphic relationship)
        Schema::create('lesson_articles', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->timestamps();
        });

        Schema::create('lesson_videos', function (Blueprint $table) {
            $table->id();
            $table->string('video_s3_key');
            $table->integer('duration')->nullable()->comment('Duration in seconds');
            $table->timestamps();
        });

        Schema::create('lesson_assignments', function (Blueprint $table) {
            $table->id();
            $table->text('instructions');
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });

        // Lessons are individual learning units within a module
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('title');
            $table->integer('order')->comment('The order of the lesson within the module');
            $table->morphs('lessonable'); // Links to quizzes, lesson_articles, lesson_videos, etc.
            $table->timestamps();
        });

        // === USER INTERACTION & DATA ===

        // Pivot table for lesson completion tracking
        Schema::create('lesson_user', function (Blueprint $table) {
            $table->primary(['user_id', 'lesson_id']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->timestamp('completed_at')->useCurrent();
        });

        // Course reviews submitted by students
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // Quiz attempts by students
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('status', ['passed', 'failed', 'in_progress'])->default('in_progress');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });

        // Individual student answers for a quiz attempt
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options')->onDelete('set null');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // Assignment submissions by students
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('lesson_assignments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('file_path');
            $table->timestamp('submitted_at')->useCurrent();
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        // Discussion threads for lessons
        Schema::create('lesson_discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('lesson_discussions')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_discussions');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('student_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('course_reviews');
        Schema::dropIfExists('lesson_user');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('lesson_assignments');
        Schema::dropIfExists('lesson_videos');
        Schema::dropIfExists('lesson_articles');
        Schema::dropIfExists('modules');
    }
};
