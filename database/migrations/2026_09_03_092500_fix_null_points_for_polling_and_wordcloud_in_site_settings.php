<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Memastikan kolom points_for_polling dan points_for_wordcloud
     * pada row yang sudah ada di tabel site_settings memiliki nilai default (5)
     * jika masih NULL. Ini terjadi karena MySQL hanya menerapkan DEFAULT
     * pada row baru, bukan pada row yang sudah ada saat kolom ditambahkan.
     */
    public function up(): void
    {
        DB::table('site_settings')
            ->whereNull('points_for_polling')
            ->update(['points_for_polling' => 5]);

        DB::table('site_settings')
            ->whereNull('points_for_wordcloud')
            ->update(['points_for_wordcloud' => 5]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada yang perlu di-rollback — operasi ini hanya memperbaiki data NULL.
    }
};
