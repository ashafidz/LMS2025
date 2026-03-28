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
        Schema::table('quiz_security_settings', function (Blueprint $table) {
            $table->boolean('detect_face_not_detected')->default(true)->after('face_detection_interval_seconds');
            $table->boolean('detect_look_left')->default(true)->after('detect_face_not_detected');
            $table->boolean('detect_look_right')->default(true)->after('detect_look_left');
            $table->boolean('detect_look_up')->default(true)->after('detect_look_right');
            $table->boolean('detect_look_down')->default(true)->after('detect_look_up');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_security_settings', function (Blueprint $table) {
            $table->dropColumn([
                'detect_face_not_detected',
                'detect_look_left',
                'detect_look_right',
                'detect_look_up',
                'detect_look_down',
            ]);
        });
    }
};
