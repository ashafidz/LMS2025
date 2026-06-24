<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->json('quiz_data')->nullable()->after('assignment_max_score')->comment('Menyimpan daftar soal, opsi, dan jawaban yang benar.');
        });
    }

    public function down(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->dropColumn(['quiz_data']);
        });
    }
};
