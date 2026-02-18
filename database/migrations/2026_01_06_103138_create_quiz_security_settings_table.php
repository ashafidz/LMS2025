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
        Schema::create('quiz_security_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id')->unique();
            $table->boolean('enable_camera_detection')->default(false);
            $table->boolean('enable_tab_detection')->default(false);
            $table->boolean('enable_question_shuffle')->default(false);
            $table->integer('camera_violation_threshold')->default(3)->comment('Batas toleransi pelanggaran kamera');
            $table->integer('tab_violation_threshold')->default(5)->comment('Batas toleransi perpindahan tab');
            $table->integer('face_detection_interval_seconds')->default(5)->comment('Interval deteksi wajah dalam detik');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('quiz_id')
                ->references('id')
                ->on('quizzes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_security_settings');
    }
};
