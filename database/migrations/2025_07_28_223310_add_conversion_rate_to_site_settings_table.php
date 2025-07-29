<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Kolom untuk rasio konversi Poin ke Diamond
            // Contoh: nilai 0.7 berarti 100 poin akan menjadi 70 diamond
            $table->decimal('point_to_diamond_rate', 8, 4)->default(1.0000)->after('points_for_assignment');
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('point_to_diamond_rate');
        });
    }
};
