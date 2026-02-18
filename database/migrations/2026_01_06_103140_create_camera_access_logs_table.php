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
        Schema::create('camera_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->timestamp('permission_requested_at');
            $table->boolean('permission_granted')->nullable()->comment('null = belum dijawab, true = granted, false = denied');
            $table->timestamp('permission_granted_at')->nullable();
            $table->text('browser_info')->nullable()->comment('User agent dan browser info');
            $table->text('error_message')->nullable()->comment('Pesan error jika akses kamera gagal');
            $table->timestamp('created_at')->useCurrent();

            // Index
            $table->index('attempt_id');

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
        Schema::dropIfExists('camera_access_logs');
    }
};
