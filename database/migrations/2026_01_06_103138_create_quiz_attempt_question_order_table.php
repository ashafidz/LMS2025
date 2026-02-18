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
        Schema::create('quiz_attempt_question_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->integer('shuffled_order')->comment('Urutan soal setelah diacak (1, 2, 3, dst)');
            $table->timestamp('created_at')->useCurrent();

            // Composite unique key (mencegah duplikasi soal dalam satu attempt)
            $table->unique(['attempt_id', 'question_id']);

            // Index untuk query cepat
            $table->index(['attempt_id', 'shuffled_order']);

            // Foreign key constraints
            $table->foreign('attempt_id')
                ->references('id')
                ->on('quiz_attempts')
                ->onDelete('cascade');

            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_question_order');
    }
};
