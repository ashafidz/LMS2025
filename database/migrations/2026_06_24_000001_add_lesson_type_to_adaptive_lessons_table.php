<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            // 'article' = materi teks (default), 'assignment' = penugasan
            $table->string('lesson_type')->default('article')->after('title');
            // Khusus untuk tipe penugasan
            $table->text('assignment_instructions')->nullable()->after('content');
            $table->integer('assignment_max_score')->default(100)->after('assignment_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->dropColumn(['lesson_type', 'assignment_instructions', 'assignment_max_score']);
        });
    }
};
