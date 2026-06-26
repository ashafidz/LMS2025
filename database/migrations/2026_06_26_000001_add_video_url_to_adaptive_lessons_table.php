<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('lesson_type')->comment('URL video (YouTube, dll) jika tipe lesson adalah video');
        });
    }

    public function down(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
