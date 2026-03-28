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
            $table->integer('violation_duration_seconds')->default(3)->after('detect_look_down');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_security_settings', function (Blueprint $table) {
            $table->dropColumn('violation_duration_seconds');
        });
    }
};
