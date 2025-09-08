<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // Kolom untuk menyimpan jadwal ketersediaan kuis
            $table->timestamp('available_from')->nullable()->after('max_attempts');
            $table->timestamp('available_to')->nullable()->after('available_from');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['available_from', 'available_to']);
        });
    }
};
