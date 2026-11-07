<?php

namespace Tests\Feature\Instructor;

use App\Models\AssignmentSubmission;
use App\Models\CourseReview;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\LessonDiscussion;
use App\Models\User;
use App\Services\HashIdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class InstructorDashboardTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function open(User $instructor)
    {
        return $this->actingAs($instructor)->withSession(['active_role' => 'instructor'])->get(route('instructor.dashboard'));
    }

    /** An assignment lesson in a new module of $course, with $submissions submitted rows. */
    private function assignmentWithSubmissions($course, string $lessonTitle, int $submitted, int $graded = 0): LessonAssignment
    {
        $module = $this->makeModule($course);
        $assignment = LessonAssignment::create(['instructions' => 'Kumpulkan', 'pass_mark' => 70]);
        Lesson::factory()->create([
            'module_id' => $module->id,
            'title' => $lessonTitle,
            'lessonable_type' => LessonAssignment::class,
            'lessonable_id' => $assignment->id,
        ]);
        for ($i = 0; $i < $submitted + $graded; $i++) {
            AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $this->makeStudent()->id,
                'file_path' => 'x/'.$i.'.pdf',
                'submitted_at' => now(),
                'status' => $i < $submitted ? 'submitted' : 'passed',
            ]);
        }

        return $assignment;
    }

    public function test_grading_list_counts_only_submitted_rows_of_own_courses_and_links_to_the_assignment(): void
    {
        $me = $this->makeInstructor(['name' => 'Rina Dosen']);
        $other = $this->makeInstructor();
        $mine = $this->makeCourse($me, ['title' => 'Kursus Milik Saya']);
        $theirs = $this->makeCourse($other, ['title' => 'Kursus Orang Lain']);
        $assignment = $this->assignmentWithSubmissions($mine, 'Tugas Esai', 3, 2);
        $this->assignmentWithSubmissions($theirs, 'Tugas Rahasia', 4);

        $this->open($me)
            ->assertOk()
            ->assertSee('3 tugas menunggu dinilai: Tugas Esai')
            ->assertSee('Kursus Milik Saya')
            ->assertSee(route('instructor.assignment.submissions', HashIdService::encode($assignment->id)), false)
            ->assertDontSee('Tugas Rahasia')
            ->assertDontSee('Kursus Orang Lain')
            ->assertDontSee('Tidak ada yang perlu ditindaklanjuti.');
    }

    public function test_grading_list_shows_at_most_five_rows(): void
    {
        $me = $this->makeInstructor();
        $course = $this->makeCourse($me);
        foreach (range(1, 7) as $n) {
            $this->assignmentWithSubmissions($course, 'Tugas Nomor '.$n, 1);
        }

        $html = $this->open($me)->assertOk()->getContent();
        $this->assertSame(5, substr_count($html, 'tugas menunggu dinilai:'));
    }

    public function test_course_state_counts_are_listed_with_indonesian_labels(): void
    {
        $me = $this->makeInstructor();
        $this->makeCourse($me, ['status' => 'pending_review']);
        $this->makeCourse($me, ['status' => 'rejected']);
        $this->makeCourse($me, ['status' => 'draft']);
        $this->makeCourse($me, ['status' => 'draft']);
        $this->makeCourse($me, ['status' => 'published']);
        $this->makeCourse($this->makeInstructor(), ['status' => 'rejected']);

        $this->open($me)
            ->assertOk()
            ->assertSee('1 kursus menunggu tinjauan')
            ->assertSee('1 kursus ditolak, perlu diperbaiki')
            ->assertSee('2 draf')
            ->assertSee(route('instructor.courses.index'), false);
    }

    public function test_nothing_to_do_shows_the_calm_message(): void
    {
        $me = $this->makeInstructor();
        $this->makeCourse($me, ['status' => 'published']);

        $this->open($me)->assertOk()->assertSee('Tidak ada yang perlu ditindaklanjuti.');
    }

    public function test_recent_discussions_exclude_own_deleted_and_foreign_posts(): void
    {
        $me = $this->makeInstructor();
        $other = $this->makeInstructor();
        $student = $this->makeStudent(['name' => 'Siswa Aktif']);
        $course = $this->makeCourse($me, ['title' => 'Kursus Diskusi']);
        $module = $this->makeModule($course);
        $lesson = $this->makeArticleLesson($module);
        $lesson->update(['title' => 'Materi Diskusi']);
        $foreignLesson = $this->makeArticleLesson($this->makeModule($this->makeCourse($other)));

        LessonDiscussion::create(['lesson_id' => $lesson->id, 'user_id' => $student->id, 'content' => '<p>Pertanyaan <b>penting</b> dari siswa</p>']);
        LessonDiscussion::create(['lesson_id' => $lesson->id, 'user_id' => $me->id, 'content' => 'Balasan dari saya sendiri']);
        LessonDiscussion::create(['lesson_id' => $lesson->id, 'user_id' => $student->id, 'content' => 'Komentar yang sudah dihapus', 'is_deleted' => true]);
        LessonDiscussion::create(['lesson_id' => $foreignLesson->id, 'user_id' => $student->id, 'content' => 'Komentar di kursus orang lain']);

        $this->open($me)
            ->assertOk()
            ->assertSee('Diskusi terbaru')
            ->assertSee('Siswa Aktif')
            ->assertSee('Pertanyaan penting dari siswa')
            ->assertSee('Materi Diskusi')
            ->assertSee(route('instructor.modules.lessons.index', HashIdService::encode($module->id)), false)
            ->assertDontSee('Balasan dari saya sendiri')
            ->assertDontSee('Komentar yang sudah dihapus')
            ->assertDontSee('Komentar di kursus orang lain');
    }

    public function test_discussion_and_review_cards_show_an_empty_message_when_there_is_nothing(): void
    {
        // The side cards stay visible (the right column is never empty); they explain what will appear there.
        $me = $this->makeInstructor();
        $this->makeCourse($me);

        $this->open($me)->assertOk()
            ->assertSee('Diskusi terbaru')->assertSee('Belum ada diskusi baru dari siswa.')
            ->assertSee('Ulasan terbaru')->assertSee('Belum ada ulasan.');
    }

    public function test_recent_reviews_show_only_own_courses_and_at_most_three(): void
    {
        $me = $this->makeInstructor();
        $other = $this->makeInstructor();
        $mine = $this->makeCourse($me, ['title' => 'Kursus Berulasan']);
        $theirs = $this->makeCourse($other, ['title' => 'Kursus Lain Berulasan']);
        foreach (range(1, 4) as $n) {
            CourseReview::create(['course_id' => $mine->id, 'user_id' => $this->makeStudent()->id, 'rating' => 5, 'comment' => 'Ulasan nomor '.$n]);
        }
        CourseReview::create(['course_id' => $theirs->id, 'user_id' => $this->makeStudent()->id, 'rating' => 1, 'comment' => 'Ulasan asing']);

        $html = $this->open($me)->assertOk()->assertSee('Ulasan terbaru')->assertDontSee('Ulasan asing')->getContent();
        $this->assertSame(3, substr_count($html, 'Ulasan nomor'));
    }

    public function test_course_without_students_says_so_instead_of_showing_an_empty_bar(): void
    {
        $me = $this->makeInstructor();
        $empty = $this->makeCourse($me, ['title' => 'Kursus Sepi']);
        $full = $this->makeCourse($me, ['title' => 'Kursus Ramai']);
        $this->enroll($this->makeStudent(), $full);

        $html = $this->open($me)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'class="progress"'));
        $this->assertStringContainsString('Belum ada siswa', $html);
        $this->assertStringContainsString('1 siswa', $html);
    }

    public function test_every_old_number_is_still_printed(): void
    {
        $me = $this->makeInstructor();
        $course = $this->makeCourse($me, ['title' => 'Kursus Angka']);
        $module = $this->makeModule($course);
        $lesson = $this->makeArticleLesson($module);
        $done = $this->makeStudent();
        $busy = $this->makeStudent();
        $this->enroll($done, $course);
        $this->enroll($busy, $course);
        $done->completedLessons()->attach($lesson->id, ['completed_at' => now()]);

        $this->open($me)
            ->assertOk()
            ->assertSee('Total kursus')
            ->assertSee('Total siswa')
            ->assertSee('Siswa selesai')
            ->assertSee('Sedang belajar')
            ->assertSee('1 selesai, 1 belum (50%)');
    }

    public function test_courses_after_the_sixth_are_still_printed_and_open_in_place(): void
    {
        $me = $this->makeInstructor();
        foreach (range(1, 8) as $i) {
            $this->makeCourse($me, ['title' => 'Kursus Nomor '.$i]);
        }

        $response = $this->open($me)->assertOk();
        foreach (range(1, 8) as $i) {
            $response->assertSee('Kursus Nomor '.$i);
        }
        $response->assertSee('Tampilkan semua (8)')->assertSee('data-crs-extra', false);
    }

    public function test_another_instructors_data_never_appears(): void
    {
        $me = $this->makeInstructor();
        $other = $this->makeInstructor();
        $theirs = $this->makeCourse($other, ['title' => 'Kursus Asing Total', 'status' => 'rejected']);
        $this->enroll($this->makeStudent(), $theirs);

        $this->open($me)
            ->assertOk()
            ->assertDontSee('Kursus Asing Total')
            ->assertSee('Tidak ada yang perlu ditindaklanjuti.');
    }
}
