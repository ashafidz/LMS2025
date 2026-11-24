<?php

namespace App\Services;

use App\Models\CourseUser;
use App\Models\PointHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The point history (point_histories) is the source of truth. course_user.points_earned is a cached total that
 * the leaderboards, module locks and badges read, and it can always be rebuilt from the history.
 */
class PointLedger
{
    /**
     * Rebuild one student's course total from the history. Returns the new total.
     */
    public static function recalculate(int $userId, int $courseId): int
    {
        return DB::transaction(function () use ($userId, $courseId) {
            $total = max(0, (int) PointHistory::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->sum('points'));

            $row = CourseUser::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->lockForUpdate()
                ->first();

            if ($row) {
                if ((int) $row->points_earned !== $total) {
                    $row->points_earned = $total;
                    $row->save();
                }
            } elseif ($total > 0) {
                $row = CourseUser::createOrFirst(
                    ['user_id' => $userId, 'course_id' => $courseId],
                    ['points_earned' => 0]
                );
                $row->points_earned = $total;
                $row->save();
            }

            return $total;
        });
    }

    /**
     * Students who have history rows on these lessons. Read before deleting lessons: the history rows are removed
     * by the foreign key cascade, and the totals must then be rebuilt with recalculateMany().
     *
     * @param  iterable<int>  $lessonIds
     * @return array<int>
     */
    public static function usersWithPointsIn(iterable $lessonIds): array
    {
        return PointHistory::whereIn('lesson_id', collect($lessonIds)->all())
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  array<int>  $userIds
     */
    public static function recalculateMany(array $userIds, int $courseId): void
    {
        foreach ($userIds as $userId) {
            self::recalculate($userId, $courseId);
        }
    }

    /**
     * Course totals that differ from the history, in courses that are not soft-deleted.
     *
     * @return Collection<int, object{user_id: int, course_id: int, counter: int, ledger: int}>
     */
    public static function drift(?int $courseId = null): Collection
    {
        $ledger = DB::table('point_histories')
            ->select('user_id', 'course_id', DB::raw('SUM(points) as ledger'))
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->groupBy('user_id', 'course_id');

        // Totals that exist (with or without history)
        $fromTotals = DB::table('course_user as cu')
            ->join('courses as c', 'c.id', '=', 'cu.course_id')
            ->whereNull('c.deleted_at')
            ->leftJoinSub($ledger, 'h', function ($join) {
                $join->on('h.user_id', '=', 'cu.user_id')->on('h.course_id', '=', 'cu.course_id');
            })
            ->when($courseId, fn ($q) => $q->where('cu.course_id', $courseId))
            ->whereRaw('cu.points_earned <> COALESCE(h.ledger, 0)')
            ->get(['cu.user_id', 'cu.course_id', 'cu.points_earned as counter', DB::raw('COALESCE(h.ledger, 0) as ledger')]);

        // History without any total row
        $withoutTotals = DB::table('point_histories as ph')
            ->join('courses as c', 'c.id', '=', 'ph.course_id')
            ->whereNull('c.deleted_at')
            ->leftJoin('course_user as cu', function ($join) {
                $join->on('cu.user_id', '=', 'ph.user_id')->on('cu.course_id', '=', 'ph.course_id');
            })
            ->whereNull('cu.id')
            ->when($courseId, fn ($q) => $q->where('ph.course_id', $courseId))
            ->groupBy('ph.user_id', 'ph.course_id')
            ->havingRaw('SUM(ph.points) <> 0')
            ->get(['ph.user_id', 'ph.course_id', DB::raw('0 as counter'), DB::raw('SUM(ph.points) as ledger')]);

        return $fromTotals->concat($withoutTotals)->map(fn ($row) => (object) [
            'user_id' => (int) $row->user_id,
            'course_id' => (int) $row->course_id,
            'counter' => (int) $row->counter,
            'ledger' => (int) $row->ledger,
        ])->values();
    }

    /**
     * Data for the student progress pages: points per lesson (summed, a manual-points lesson can have several
     * rows), the rows outside any lesson, and the course total from the history.
     *
     * @return array{0: Collection<int, int>, 1: Collection<int, PointHistory>, 2: int}
     */
    public static function progressFor(int $userId, int $courseId): array
    {
        $histories = PointHistory::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->orderBy('created_at')
            ->get();

        $lessonPoints = $histories->whereNotNull('lesson_id')
            ->groupBy('lesson_id')
            ->map(fn ($rows) => (int) $rows->sum('points'));

        $otherPointHistories = $histories->whereNull('lesson_id')->values();

        return [$lessonPoints, $otherPointHistories, (int) $histories->sum('points')];
    }
}
