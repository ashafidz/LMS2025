<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data fix (ADR-016): remove every old "Membeli kursus: ..." point row. When the order was really paid
     * (final_amount > 0) the points become diamonds at the point-to-diamond rate; a free order (100% coupon,
     * Rp0) gives nothing. Courses whose points were already converted to diamonds are skipped.
     * Every moved row is kept in purchase_point_migrations so down() can restore it.
     */
    public function up(): void
    {
        Schema::create('purchase_point_migrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('point_history_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->integer('points');
            $table->string('description');
            $table->timestamp('history_created_at')->nullable();
            $table->timestamp('history_updated_at')->nullable();
            $table->integer('diamonds')->default(0);
            $table->unsignedBigInteger('diamond_history_id')->nullable();
            $table->timestamps();
        });

        $rate = (float) (DB::table('site_settings')->value('point_to_diamond_rate') ?? 0);
        $summary = ['rows' => 0, 'skipped' => 0, 'paid' => 0, 'free' => 0, 'diamonds' => 0, 'rate' => $rate];

        $rows = DB::table('point_histories')
            ->whereNull('lesson_id')
            ->where('description', 'like', 'Membeli kursus:%')
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            DB::transaction(function () use ($row, $rate, &$summary) {
                $summary['rows']++;

                $converted = DB::table('course_user')
                    ->where('user_id', $row->user_id)
                    ->where('course_id', $row->course_id)
                    ->value('is_converted_to_diamond');

                if ($converted) {
                    $summary['skipped']++;

                    return;
                }

                $order = DB::table('orders')
                    ->join('order_items', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.user_id', $row->user_id)
                    ->where('order_items.course_id', $row->course_id)
                    ->where('orders.status', 'paid')
                    ->orderByDesc('orders.id')
                    ->first(['orders.id', 'orders.final_amount']);

                $paid = $order && (float) $order->final_amount > 0;
                $diamonds = $paid ? (int) floor((int) $row->points * $rate) : 0;
                $summary[$paid ? 'paid' : 'free']++;

                $diamondHistoryId = null;
                if ($diamonds > 0) {
                    $title = DB::table('courses')->where('id', $row->course_id)->value('title') ?? '';
                    DB::table('users')->where('id', $row->user_id)->increment('diamond_balance', $diamonds);
                    $diamondHistoryId = DB::table('diamond_histories')->insertGetId([
                        'user_id' => $row->user_id,
                        'diamonds' => $diamonds,
                        'description' => mb_substr('Pengalihan poin pembelian kursus: '.$title, 0, 255),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $summary['diamonds'] += $diamonds;
                }

                $current = (int) DB::table('course_user')
                    ->where('user_id', $row->user_id)
                    ->where('course_id', $row->course_id)
                    ->value('points_earned');
                DB::table('course_user')
                    ->where('user_id', $row->user_id)
                    ->where('course_id', $row->course_id)
                    ->update(['points_earned' => max(0, $current - (int) $row->points)]);

                DB::table('purchase_point_migrations')->insert([
                    'point_history_id' => $row->id,
                    'user_id' => $row->user_id,
                    'course_id' => $row->course_id,
                    'points' => $row->points,
                    'description' => $row->description,
                    'history_created_at' => $row->created_at,
                    'history_updated_at' => $row->updated_at,
                    'diamonds' => $diamonds,
                    'diamond_history_id' => $diamondHistoryId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('point_histories')->where('id', $row->id)->delete();
            });
        }

        logger()->info('[migration] move_purchase_points_to_diamonds', $summary);
    }

    public function down(): void
    {
        foreach (DB::table('purchase_point_migrations')->orderBy('id')->get() as $moved) {
            DB::transaction(function () use ($moved) {
                DB::table('point_histories')->insert([
                    'user_id' => $moved->user_id,
                    'course_id' => $moved->course_id,
                    'lesson_id' => null,
                    'points' => $moved->points,
                    'description' => $moved->description,
                    'created_at' => $moved->history_created_at,
                    'updated_at' => $moved->history_updated_at,
                ]);

                $current = DB::table('course_user')
                    ->where('user_id', $moved->user_id)
                    ->where('course_id', $moved->course_id)
                    ->value('points_earned');
                if ($current !== null) {
                    DB::table('course_user')
                        ->where('user_id', $moved->user_id)
                        ->where('course_id', $moved->course_id)
                        ->update(['points_earned' => (int) $current + (int) $moved->points]);
                }

                if ($moved->diamond_history_id) {
                    $balance = (int) DB::table('users')->where('id', $moved->user_id)->value('diamond_balance');
                    DB::table('users')->where('id', $moved->user_id)
                        ->update(['diamond_balance' => max(0, $balance - (int) $moved->diamonds)]);
                    DB::table('diamond_histories')->where('id', $moved->diamond_history_id)->delete();
                }
            });
        }

        Schema::dropIfExists('purchase_point_migrations');
    }
};
