<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Badge ID 1 dulu diberikan oleh kode (lulus 10 kuis). Pindahkan aturannya ke tabel badge_rules,
     * dan tandai semua pemilik lama sebagai sudah dilihat agar siswa tidak dibanjiri notifikasi.
     */
    public function up(): void
    {
        DB::table('badge_user')->whereNull('seen_at')->update(['seen_at' => now()]);

        $legacyExists = DB::table('badges')->where('id', 1)->exists();
        $hasRule = DB::table('badge_rules')->where('badge_id', 1)->exists();

        if ($legacyExists && ! $hasRule) {
            DB::table('badge_rules')->insert([
                'badge_id' => 1,
                'metric' => 'quiz_passed',
                'target' => 10,
                'scope' => 'all',
                'scope_id' => null,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('badge_rules')
            ->where('badge_id', 1)
            ->where('metric', 'quiz_passed')
            ->where('target', 10)
            ->where('scope', 'all')
            ->delete();
    }
};
