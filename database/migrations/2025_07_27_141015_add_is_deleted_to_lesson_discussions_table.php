<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lesson_discussions', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('content');
        });
    }

    public function down()
    {
        Schema::table('lesson_discussions', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });
    }
};
