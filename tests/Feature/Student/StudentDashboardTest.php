<?php

namespace Tests\Feature\Student;

use App\Models\Badge;
use App\Models\BadgeRule;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function open(User $student)
    {
        return $this->actingAs($student)->withSession(['active_role' => 'student'])->get(route('student.dashboard'));
    }

    private function courseWithLessons(User $instructor, int $lessons, string $title): Course
    {
        $course = $this->makeCourse($instructor, ['title' => $title]);
        $module = $this->makeModule($course);
        for ($i = 0; $i < $lessons; $i++) {
            $this->makeArticleLesson($module);
        }

        return $course;
    }

    public function test_student_without_enrolment_sees_the_empty_state(): void
    {
        $this->open($this->makeStudent(['name' => 'Budi Santoso']))
            ->assertOk()
            ->assertSee('Halo, Budi!')
            ->assertSee('Belum ada kursus. Yuk mulai belajar.')
            ->assertSee(route('courses'), false);
    }

    public function test_enrolled_student_sees_progress_and_totals(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $half = $this->courseWithLessons($instructor, 4, 'Kursus Setengah');
        $empty = $this->courseWithLessons($instructor, 0, 'Kursus Kosong');
        $this->enroll($student, $half);
        $this->enroll($student, $empty);
        $half->lessons()->limit(2)->get()->each(fn ($l) => $student->completedLessons()->attach($l->id, ['completed_at' => now()]));

        $this->open($student)
            ->assertOk()
            ->assertSee('Kursus Setengah')
            ->assertSee('2 dari 4 materi selesai')
            ->assertSee('50%')
            ->assertSee('Belum ada materi')
            ->assertSee(route('student.courses.show', $half->slug), false);
    }

    public function test_finished_course_is_not_in_continue_list_and_shows_the_done_pill(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $done = $this->courseWithLessons($instructor, 2, 'Kursus Tuntas');
        $this->enroll($student, $done);
        $done->lessons()->get()->each(fn ($l) => $student->completedLessons()->attach($l->id, ['completed_at' => now()]));

        $this->open($student)
            ->assertOk()
            ->assertSee('Semua kursusmu sudah selesai')
            ->assertSee('Selesai')
            ->assertSee('Kursus Tuntas');
    }

    public function test_almost_finished_course_shows_99_not_100_percent(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $course = $this->courseWithLessons($instructor, 200, 'Kursus Panjang');
        $this->enroll($student, $course);
        $course->lessons()->limit(199)->get()->each(fn ($l) => $student->completedLessons()->attach($l->id, ['completed_at' => now()]));

        $this->open($student)
            ->assertOk()
            ->assertSee('199 dari 200 materi selesai')
            ->assertSee('99%')
            ->assertDontSee('100%')
            ->assertDontSee('Selesai</span>', false);
    }

    public function test_dashboard_does_not_query_per_course(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $count = function () use ($student) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->open($student)->assertOk();

            return count(DB::getQueryLog());
        };

        $this->enroll($student, $this->courseWithLessons($instructor, 2, 'Satu'));
        $count(); // warm-up: cache and settings queries run once
        $few = $count();
        foreach (['Dua', 'Tiga', 'Empat', 'Lima'] as $title) {
            $this->enroll($student, $this->courseWithLessons($instructor, 2, $title));
        }

        $this->assertSame($few, $count());
    }

    public function test_next_lesson_of_a_course_with_progress_is_shown_with_its_type(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $course = $this->makeCourse($instructor, ['title' => 'Kursus Bertahap']);
        $module = $this->makeModule($course);
        $first = $this->makeArticleLesson($module);
        $second = $this->makeArticleLesson($module);
        $second->update(['title' => 'Materi Kedua Saya']);
        $this->enroll($student, $course);
        $student->completedLessons()->attach($first->id, ['completed_at' => now()]);

        $this->open($student)
            ->assertOk()
            ->assertSee('Materi berikutnya')
            ->assertSee('Materi Kedua Saya (Artikel)')
            ->assertDontSee('Modul berikutnya masih terkunci');
    }

    public function test_locked_next_module_shows_the_lock_message_instead_of_the_title(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $course = $this->makeCourse($instructor, ['title' => 'Kursus Berkunci']);
        $open = $this->makeModule($course);
        $locked = $this->makeModule($course, 50);
        $done = $this->makeArticleLesson($open);
        $hidden = $this->makeArticleLesson($locked);
        $hidden->update(['title' => 'Judul Rahasia Modul Terkunci']);
        $this->enroll($student, $course);
        $student->completedLessons()->attach($done->id, ['completed_at' => now()]);

        $this->open($student)
            ->assertSee('Modul berikutnya masih terkunci')
            ->assertDontSee('Judul Rahasia Modul Terkunci');

        // With enough points the same lesson is shown (same rule as the learning page)
        $student->coursePoints()->attach($course->id, ['points_earned' => 50]);
        $this->open($student)
            ->assertDontSee('Modul berikutnya masih terkunci')
            ->assertSee('Judul Rahasia Modul Terkunci (Artikel)');
    }

    public function test_recent_points_are_listed_with_sign_course_and_fallback_text(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $course = $this->courseWithLessons($instructor, 1, 'Kursus Poin');
        $this->enroll($student, $course);
        PointHistory::create(['user_id' => $student->id, 'course_id' => $course->id, 'points' => 5, 'description' => 'Menyelesaikan Artikel']);
        PointHistory::create(['user_id' => $student->id, 'course_id' => $course->id, 'points' => -3, 'description' => '']);

        $this->open($student)
            ->assertSee('Aktivitas poin')
            ->assertSee('+5')
            ->assertSee('-3')
            ->assertSee('Menyelesaikan Artikel')
            ->assertSee('Poin dari kursus')
            ->assertSee(route('student.points.index'), false);
    }

    public function test_points_card_explains_when_there_is_no_history_yet(): void
    {
        $this->open($this->makeStudent())
            ->assertSee('Aktivitas poin')
            ->assertSee('Belum ada aktivitas poin.');
    }

    public function test_next_badge_is_the_closest_one_not_owned_yet(): void
    {
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor();
        $course = $this->courseWithLessons($instructor, 4, 'Kursus Lencana');
        $this->enroll($student, $course);
        $course->lessons()->limit(2)->get()->each(fn ($l) => $student->completedLessons()->attach($l->id, ['completed_at' => now()]));

        $far = Badge::factory()->create(['title' => 'Lencana Jauh']);
        BadgeRule::factory()->create(['badge_id' => $far->id, 'metric' => 'lessons_completed', 'target' => 20]);
        $near = Badge::factory()->create(['title' => 'Lencana Dekat']);
        BadgeRule::factory()->create(['badge_id' => $near->id, 'metric' => 'lessons_completed', 'target' => 4]);
        $owned = Badge::factory()->create(['title' => 'Lencana Sudah Punya']);
        BadgeRule::factory()->create(['badge_id' => $owned->id, 'metric' => 'lessons_completed', 'target' => 2]);
        $student->badges()->attach($owned->id, ['seen_at' => now()]);

        $this->open($student)
            ->assertSee('Lencana berikutnya')
            ->assertSee('Lencana Dekat')
            ->assertDontSee('Lencana Jauh')
            ->assertDontSee('Lencana Sudah Punya')
            ->assertSee(route('student.badges.index'), false);
    }

    public function test_badge_card_explains_when_no_badge_applies(): void
    {
        $this->open($this->makeStudent())
            ->assertDontSee('Lencana berikutnya')
            ->assertSee('Belum ada lencana yang bisa dikumpulkan saat ini.');
    }

    public function test_recommendations_exclude_enrolled_and_unpublished_courses_and_prefer_the_same_category(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $mine = CourseCategory::factory()->create();
        $other = CourseCategory::factory()->create();
        $enrolled = $this->makeCourse($instructor, ['title' => 'Sudah Saya Ikuti', 'category_id' => $mine->id]);
        $this->enroll($student, $enrolled);
        $this->makeCourse($instructor, ['title' => 'Satu Kategori', 'category_id' => $mine->id]);
        $this->makeCourse($instructor, ['title' => 'Kategori Lain', 'category_id' => $other->id]);
        $this->makeCourse($instructor, ['title' => 'Masih Draf', 'category_id' => $mine->id, 'status' => 'draft']);

        $response = $this->open($student)->assertOk();
        $response->assertSee('Kursus untuk kamu')
            ->assertSee('Satu Kategori')
            ->assertSee('Kategori Lain')
            ->assertDontSee('Masih Draf');
        // "Sudah Saya Ikuti" is listed once, in "Kursus saya", never as a recommendation
        $this->assertSame(1, substr_count($response->getContent(), 'Sudah Saya Ikuti</strong>'));
    }

    public function test_recommendations_are_hidden_when_there_is_nothing_to_recommend(): void
    {
        $this->open($this->makeStudent())->assertDontSee('Kursus untuk kamu');
    }

    public function test_new_dashboard_data_does_not_add_a_query_per_course(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();
        $count = function () use ($student) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->open($student)->assertOk();

            return count(DB::getQueryLog());
        };
        $addCourse = function (string $title) use ($instructor, $student) {
            $course = $this->makeCourse($instructor, ['title' => $title]);
            $first = $this->makeModule($course);
            $this->makeArticleLesson($first);
            $this->makeArticleLesson($this->makeModule($course, 10));
            $this->enroll($student, $course);
            PointHistory::create(['user_id' => $student->id, 'course_id' => $course->id, 'points' => 2, 'description' => 'x']);
        };

        $addCourse('Satu');
        $this->makeCourse($instructor, ['title' => 'Rekomendasi A']);
        $count(); // warm-up
        $few = $count();
        foreach (['Dua', 'Tiga', 'Empat', 'Lima'] as $title) {
            $addCourse($title);
        }

        $this->assertSame($few, $count());
    }
}
