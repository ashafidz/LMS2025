<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->dropColumn('document_path');
        });
    }
};
