<?php

namespace App\Console\Commands;

use App\Services\PointLedger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Checks the cached totals against their history (ADR-016):
 * - course_user.points_earned must equal SUM(point_histories); --fix rebuilds it from the history.
 * - users.diamond_balance must equal SUM(diamond_histories); only reported, never changed automatically.
 */
class ReconcilePoints extends Command
{
    protected $signature = 'points:reconcile {--fix : Rebuild point totals that differ from the history} {--course= : Only this course id}';

    protected $description = 'Compare point totals and diamond balances with their history';

    public function handle(): int
    {
        $courseId = $this->option('course') !== null ? (int) $this->option('course') : null;
        $fix = (bool) $this->option('fix');

        $drift = PointLedger::drift($courseId);

        if ($drift->isEmpty()) {
            $this->info('Poin: semua total sama dengan riwayat.');
        } else {
            $this->warn('Poin: '.$drift->count().' total berbeda dengan riwayat.');
            $this->table(['user_id', 'course_id', 'total', 'riwayat'], $drift->map(fn ($row) => [
                $row->user_id, $row->course_id, $row->counter, $row->ledger,
            ])->all());

            foreach ($drift as $row) {
                if ($fix) {
                    PointLedger::recalculate($row->user_id, $row->course_id);
                    Log::warning('[points:reconcile] fixed', (array) $row);
                } else {
                    Log::warning('[points:reconcile] drift', (array) $row);
                }
            }

            if ($fix) {
                $this->info('Poin: '.$drift->count().' total dihitung ulang dari riwayat.');
            }
        }

        // Diamonds: report only
        $diamondDrift = DB::table('users as u')
            ->leftJoinSub(
                DB::table('diamond_histories')->select('user_id', DB::raw('SUM(diamonds) as total'))->groupBy('user_id'),
                'd',
                'd.user_id',
                '=',
                'u.id'
            )
            ->whereRaw('u.diamond_balance <> COALESCE(d.total, 0)')
            ->get(['u.id as user_id', 'u.diamond_balance as balance', DB::raw('COALESCE(d.total, 0) as history')]);

        if ($diamondDrift->isEmpty()) {
            $this->info('Diamond: semua saldo sama dengan riwayat.');
        } else {
            $this->warn('Diamond: '.$diamondDrift->count().' saldo berbeda dengan riwayat (tidak diubah).');
            $this->table(['user_id', 'saldo', 'riwayat'], $diamondDrift->map(fn ($row) => [
                $row->user_id, $row->balance, $row->history,
            ])->all());

            foreach ($diamondDrift as $row) {
                Log::warning('[points:reconcile] diamond drift', (array) $row);
            }
        }

        return self::SUCCESS;
    }
}
