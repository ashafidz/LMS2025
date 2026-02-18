<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom scaled_score untuk menyimpan skor dalam skala 0-100.
     * Rumus: (score / total_max_score) * 100
     * Dihitung sekali saat student selesai mengerjakan quiz.
     */
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->decimal('scaled_score', 5, 2)->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('scaled_score');
        });
    }
};
