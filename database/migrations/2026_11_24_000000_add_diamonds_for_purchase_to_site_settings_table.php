<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A paid course purchase now gives diamonds instead of points (ADR-016). The starting value is the old
     * purchase points converted with the point-to-diamond rate. points_for_purchase stays but is no longer used.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedInteger('diamonds_for_purchase')->default(0);
        });

        foreach (DB::table('site_settings')->get(['id', 'points_for_purchase', 'point_to_diamond_rate']) as $row) {
            DB::table('site_settings')->where('id', $row->id)->update([
                'diamonds_for_purchase' => max(0, (int) floor((int) $row->points_for_purchase * (float) $row->point_to_diamond_rate)),
            ]);
        }

        // Settings are cached forever for every view; drop the copy without the new column
        Cache::forget('site_settings');
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('diamonds_for_purchase');
        });

        Cache::forget('site_settings');
    }
};
