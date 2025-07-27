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
            // Kolom baru untuk opsi batas waktu
            // Default 'false' berarti kuis akan berakhir otomatis
            $table->boolean('allow_exceed_time_limit')->default(false)->after('time_limit');
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
            $table->dropColumn('allow_exceed_time_limit');
        });
    }
};
