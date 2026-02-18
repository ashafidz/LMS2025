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
        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->enum('violation_type', [
                'tab_switch',
                'face_not_detected',
                'look_left',
                'look_right',
                'look_down',
                'look_up'
            ]);
            $table->timestamp('violation_timestamp');
            $table->integer('duration_seconds')->nullable()->comment('Durasi pelanggaran (untuk face detection)');
            $table->string('screenshot_path', 500)->nullable()->comment('Path foto/screenshot saat pelanggaran');
            $table->json('additional_data')->nullable()->comment('Data tambahan seperti koordinat wajah, confidence score');
            $table->timestamp('created_at')->useCurrent();

            // Indexes untuk query cepat
            $table->index(['attempt_id', 'violation_type']);
            $table->index(['attempt_id', 'violation_timestamp']);

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
        Schema::dropIfExists('monitoring_logs');
    }
};
