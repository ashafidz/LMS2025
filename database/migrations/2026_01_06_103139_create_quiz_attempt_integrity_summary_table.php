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
        Schema::create('quiz_attempt_integrity_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id')->unique();
            $table->integer('total_tab_switches')->default(0);
            $table->integer('total_face_violations')->default(0)->comment('Total semua pelanggaran face detection');
            $table->integer('face_not_detected_count')->default(0);
            $table->integer('look_left_count')->default(0);
            $table->integer('look_right_count')->default(0);
            $table->integer('look_down_count')->default(0);
            $table->integer('look_up_count')->default(0);
            $table->decimal('integrity_score', 5, 2)->nullable()->comment('Skor integritas 0-100');
            $table->enum('risk_level', ['low', 'medium', 'high'])->nullable();
            $table->boolean('flagged_for_review')->default(false)->comment('Flag untuk instructor review');
            $table->timestamps();

            // Indexes
            $table->index('integrity_score');
            $table->index('risk_level');
            $table->index('flagged_for_review');

            // Foreign key constraint
            $table->foreign('attempt_id')
                ->references('id')
                ->on('quiz_attempts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_integrity_summary');
    }
};
