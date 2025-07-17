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
        // Mengganti nama tabel dari 'lesson_powerpoints' menjadi 'lesson_documents'
        Schema::rename('lesson_powerpoints', 'lesson_documents');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Mengembalikan nama tabel jika migrasi di-rollback
        Schema::rename('lesson_documents', 'lesson_powerpoints');
    }
};
