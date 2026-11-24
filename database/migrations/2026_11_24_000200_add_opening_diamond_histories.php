<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DESCRIPTION = 'Saldo awal (penyesuaian data)';

    /**
     * Data fix: a diamond balance must equal the sum of its diamond history. For users where it does not
     * (an opening balance set without history), add one history row for the difference. Balances are not changed.
     */
    public function up(): void
    {
        $sums = DB::table('diamond_histories')
            ->select('user_id', DB::raw('SUM(diamonds) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        DB::table('users')->orderBy('id')->select('id', 'diamond_balance')->chunk(500, function ($users) use ($sums) {
            foreach ($users as $user) {
                $difference = (int) $user->diamond_balance - (int) ($sums[$user->id] ?? 0);
                if ($difference !== 0) {
                    DB::table('diamond_histories')->insert([
                        'user_id' => $user->id,
                        'diamonds' => $difference,
                        'description' => self::DESCRIPTION,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('diamond_histories')->where('description', self::DESCRIPTION)->delete();
    }
};
