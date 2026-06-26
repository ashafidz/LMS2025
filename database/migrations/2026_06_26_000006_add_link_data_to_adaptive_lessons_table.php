<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->json('link_data')->nullable()->after('document_path');
        });
    }

    public function down(): void
    {
        Schema::table('adaptive_lessons', function (Blueprint $table) {
            $table->dropColumn('link_data');
        });
    }
};
