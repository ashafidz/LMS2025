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
        // A topic or category for questions, owned by an instructor
        Schema::create('question_topics', function (Blueprint $table) {
            $table->id();
            // Ensures that only instructors can create topics. Links to the users table.
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // The question itself, belonging to a topic
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('question_topics')->onDelete('cascade');
            $table->text('question_text'); // For drag-and-drop, this will contain placeholders like [[BLANK_1]]
            // Added new question types to the ENUM list
            $table->enum('question_type', [
                'multiple_choice_single', // Radio buttons
                'multiple_choice_multiple', // Checkboxes
                'true_false', // Radio buttons with True/False options
                'drag_and_drop' // Fill in the blanks
            ])->default('multiple_choice_single');
            $table->integer('score')->default(10);
            $table->timestamps();
            $table->softDeletes();
        });

        // The options/answers for a question
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->text('option_text'); // The text for the option (e.g., "Paris") or the draggable word
            $table->boolean('is_correct')->default(false); // For multiple choice and T/F, indicates the correct answer. For drag-and-drop, indicates a correct word vs. a distractor.
            // This field links a draggable answer to a specific blank in the question text.
            $table->string('correct_gap_identifier')->nullable(); // e.g., "BLANK_1"
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table to link questions from the bank to a specific quiz
        Schema::create('quiz_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->integer('order')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_question');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_topics');
    }
};
