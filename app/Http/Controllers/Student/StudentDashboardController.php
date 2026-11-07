<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Services\BadgeService;
use App\Services\LessonAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    /**
     * Menampilkan beranda siswa: kursus yang sedang dipelajari, progres, poin, diamond, dan sertifikat.
     */
    public function index()
    {
        $user = Auth::user();

        // Satu query untuk semua kursus yang diikuti (dengan jumlah materi dan yang selesai), lalu disaring di PHP.
        $enrolled = $this->enrollmentsWithProgress($user)
            ->latest('course_enrollments.created_at')
            ->get();

        $continue = $enrolled
            ->filter(fn ($course) => $course->lessons_count > 0 && $course->completed_lessons_count < $course->lessons_count)
            ->take(3)
            ->values();

        $myCourses = $enrolled->take(5)->values();

        // Sumber yang sama dengan halaman Poin, Diamond, dan Sertifikat.
        $points = $user->coursePoints()->sum('points_earned');
        $diamonds = $user->diamond_balance;
        $certificates = $user->certificates()->count();

        return view('student.dashboard', [
            'user' => $user,
            'enrolledCount' => $enrolled->count(),
            'continue' => $continue,
            'myCourses' => $myCourses,
            'points' => $points,
            'diamonds' => $diamonds,
            'certificates' => $certificates,
            'nextLessons' => $this->nextLessons($user, $continue),
            'recentPoints' => $user->pointHistories()->with('course:id,title')->latest()->take(5)->get(),
            'nextBadge' => $this->nextBadge($user),
            // For the empty message of the badge card: all earned, or none to collect yet.
            'badgesOwned' => $user->badges()->count(),
            'recommended' => $this->recommendedCourses($enrolled),
        ]);
    }

    /**
     * The next lesson to do in each course of $continue, keyed by course id: ['title', 'type', 'locked'].
     * Fixed number of queries: lessons of all those courses, completed lesson ids, course points.
     * The lock rule is LessonAccess::meetsPointsRequirement(), the same one the learning page uses.
     *
     * @return array<int, array{title: string, type: string, locked: bool}>
     */
    private function nextLessons($user, Collection $continue): array
    {
        if ($continue->isEmpty()) {
            return [];
        }

        $courseIds = $continue->pluck('id');

        $lessons = Lesson::query()
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->whereIn('modules.course_id', $courseIds)
            ->orderBy('modules.order')->orderBy('modules.id')->orderBy('lessons.order')->orderBy('lessons.id')
            ->get(['lessons.id', 'lessons.title', 'lessons.lessonable_type', 'modules.course_id', 'modules.points_required']);

        $done = DB::table('lesson_user')
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->pluck('lesson_id')
            ->flip();

        $points = $user->coursePoints()->whereIn('courses.id', $courseIds)->get()
            ->mapWithKeys(fn ($c) => [$c->id => (int) $c->pivot->points_earned]);

        $next = [];
        foreach ($lessons as $lesson) {
            $courseId = (int) $lesson->course_id;
            if (isset($next[$courseId]) || $done->has($lesson->id)) {
                continue;
            }
            $next[$courseId] = [
                'title' => $lesson->title,
                'type' => Lesson::typeLabel($lesson->lessonable_type),
                'locked' => ! LessonAccess::meetsPointsRequirement(
                    (int) ($points[$courseId] ?? 0),
                    new Module(['points_required' => $lesson->points_required])
                ),
            ];
        }

        return $next;
    }

    /**
     * The closest badge the student does not own yet, with its progress. Same source as the badges page
     * (active badges that have rules, progress from BadgeService::progressFor). Highest summed current/target wins; ties go to the lowest id.
     *
     * @return array{badge: Badge, current: int, target: int, ratio: float, rules: array}|null
     */
    private function nextBadge($user): ?array
    {
        $badges = Badge::where('is_active', true)
            ->whereHas('rules')
            ->whereDoesntHave('users', fn ($q) => $q->where('users.id', $user->id))
            ->with('rules')
            ->orderBy('id')
            ->get();

        $best = null;
        foreach ($badges as $badge) {
            $progress = BadgeService::progressFor($user, $badge, true);
            $target = array_sum(array_column($progress['rules'], 'target'));
            if ($target <= 0) {
                continue;
            }
            $current = array_sum(array_map(fn ($r) => min($r['current'], $r['target']), $progress['rules']));
            $ratio = $current / $target;
            if ($best === null || $ratio > $best['ratio']) {
                $best = ['badge' => $badge, 'ratio' => $ratio, 'current' => $current, 'target' => $target, 'rules' => $progress['rules']];
            }
        }

        return $best;
    }

    /**
     * Up to 3 published courses the student is not enrolled in: first from the categories of the enrolled courses, then the newest.
     */
    private function recommendedCourses(Collection $enrolled): Collection
    {
        $enrolledIds = $enrolled->pluck('id');
        $categoryIds = $enrolled->pluck('category_id')->filter()->unique();

        $base = fn () => Course::query()
            ->where('status', 'published')
            ->whereNotIn('id', $enrolledIds)
            ->with('instructor:id,name', 'category:id,name');

        $picked = $categoryIds->isEmpty()
            ? collect()
            : $base()->whereIn('category_id', $categoryIds)->latest()->take(3)->get();

        if ($picked->count() < 3) {
            $picked = $picked->concat(
                $base()->whereNotIn('id', $picked->pluck('id'))->latest()->take(3 - $picked->count())->get()
            );
        }

        return $picked->values();
    }

    /**
     * Menampilkan halaman "Kursus Saya"
     */
    public function myCourses()
    {
        $user = Auth::user();

        // Ambil semua kursus di mana pengguna terdaftar
        $enrolledCourses = $this->enrollmentsWithProgress($user)
            ->latest('course_enrollments.created_at')
            ->simplePaginate(9);

        return view('student.courses.index', compact('enrolledCourses'));
    }

    /**
     * Kursus yang diikuti pengguna beserta jumlah materi dan materi yang sudah diselesaikan.
     */
    private function enrollmentsWithProgress($user)
    {
        return $user->enrollments()
            ->with('instructor', 'category')
            // Menghitung total pelajaran di setiap kursus
            ->withCount('lessons')
            // Menghitung pelajaran yang sudah diselesaikan oleh pengguna ini
            ->withCount(['lessons as completed_lessons_count' => function ($query) use ($user) {
                $query->whereHas('completedBy', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id);
                });
            }]);
    }
}
