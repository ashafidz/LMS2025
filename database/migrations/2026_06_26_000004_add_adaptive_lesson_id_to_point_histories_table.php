<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_histories', function (Blueprint $table) {
            $table->foreignId('adaptive_lesson_id')->nullable()->constrained('adaptive_lessons')->onDelete('cascade')->after('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::table('point_histories', function (Blueprint $table) {
            $table->dropForeign(['adaptive_lesson_id']);
            $table->dropColumn('adaptive_lesson_id');
        });
    }
};
