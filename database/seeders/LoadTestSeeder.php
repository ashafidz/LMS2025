<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\LessonArticle;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizSecuritySetting;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Data for the local k6 load test (scripts/loadtest). Never runs in production.
 * Not called from DatabaseSeeder: php artisan db:seed --class=LoadTestSeeder
 */
class LoadTestSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('LoadTestSeeder must not run in production.');
        }

        $this->call([RoleSeeder::class, SiteSettingSeeder::class]);

        $studentCount = (int) env('LOADTEST_STUDENTS', 150);
        $password = Hash::make(self::PASSWORD);

        $instructor = User::updateOrCreate(
            ['email' => 'loadtest-instructor@example.test'],
            ['name' => 'Load Test Instructor', 'password' => $password, 'email_verified_at' => now()]
        );
        $instructor->syncRoles(['instructor']);

        $category = CourseCategory::firstOrCreate(['slug' => 'load-test'], ['name' => 'Load Test']);

        $course = Course::updateOrCreate(['slug' => 'load-test-course'], [
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'Load Test Course',
            'description' => 'Course used by the local load test.',
            'price' => 0,
            'status' => 'published',
            'availability_type' => 'lifetime',
            'payment_type' => 'money',
            'diamond_price' => 0,
        ]);

        // 3 modules x 5 article lessons
        $articleLessons = [];
        for ($m = 1; $m <= 3; $m++) {
            $module = Module::firstOrCreate(
                ['course_id' => $course->id, 'order' => $m],
                ['title' => "Modul {$m}", 'points_required' => 0]
            );
            for ($l = 1; $l <= 5; $l++) {
                $title = "Materi {$m}.{$l}";
                $lesson = Lesson::where('module_id', $module->id)->where('title', $title)->first();
                if (! $lesson) {
                    $article = LessonArticle::firstOrCreate(['content' => "<p>Isi materi {$m}.{$l} untuk load test.</p>"]);
                    $lesson = Lesson::firstOrCreate(
                        ['module_id' => $module->id, 'title' => $title],
                        ['order' => $l, 'lessonable_type' => LessonArticle::class, 'lessonable_id' => $article->id]
                    );
                }
                $articleLessons[] = $lesson;
            }
        }

        // Quiz lesson with 20 single-choice questions in the last module
        $quizLesson = Lesson::where('module_id', $module->id)->where('title', 'Kuis Load Test')->first();
        if ($quizLesson) {
            $quiz = $quizLesson->lessonable;
        } else {
            $quiz = Quiz::firstOrCreate(['title' => 'Kuis Load Test'], [
                'description' => 'Kuis untuk load test.',
                'pass_mark' => 70,
                'time_limit' => 30,
                'allow_exceed_time_limit' => false,
                'reveal_answers' => false,
                'max_attempts' => null,
            ]);
            $quizLesson = Lesson::firstOrCreate(
                ['module_id' => $module->id, 'title' => 'Kuis Load Test'],
                ['order' => 6, 'lessonable_type' => Quiz::class, 'lessonable_id' => $quiz->id]
            );
        }

        // Camera and tab proctoring on (thresholds high so the load test is never blocked)
        QuizSecuritySetting::updateOrCreate(['quiz_id' => $quiz->id], [
            'enable_camera_detection' => true,
            'enable_tab_detection' => true,
            'camera_violation_threshold' => 100000,
            'tab_violation_threshold' => 100000,
        ]);

        $topic = QuestionTopic::firstOrCreate(
            ['instructor_id' => $instructor->id, 'name' => 'Load Test Topic'],
            ['available_for_all_courses' => true]
        );
        for ($q = 1; $q <= 20; $q++) {
            $question = Question::firstOrCreate(
                ['topic_id' => $topic->id, 'question_text' => "Soal load test {$q}: 2 + {$q} = ?"],
                ['score' => 5, 'question_type' => 'multiple_choice_single']
            );
            foreach ([2 + $q, 3 + $q, 4 + $q, 5 + $q] as $i => $value) {
                QuestionOption::firstOrCreate(
                    ['question_id' => $question->id, 'option_text' => (string) $value],
                    ['is_correct' => $i === 0]
                );
            }
            $quiz->questions()->syncWithoutDetaching([$question->id => ['order' => $q]]);
        }

        // Second quiz used only by scripts/loadtest/race.js (parallel "begin" requests)
        $raceLesson = Lesson::where('module_id', $module->id)->where('title', 'Kuis Race')->first();
        $raceQuiz = $raceLesson?->lessonable ?? Quiz::firstOrCreate(['title' => 'Kuis Race'], [
            'description' => 'Kuis untuk tes race condition.',
            'pass_mark' => 70,
            'time_limit' => 30,
            'allow_exceed_time_limit' => false,
            'reveal_answers' => false,
            'max_attempts' => 1,
        ]);
        Lesson::firstOrCreate(
            ['module_id' => $module->id, 'title' => 'Kuis Race'],
            ['order' => 7, 'lessonable_type' => Quiz::class, 'lessonable_id' => $raceQuiz->id]
        );
        $raceQuiz->questions()->syncWithoutDetaching($quiz->questions()->pluck('questions.id')->mapWithKeys(fn ($id, $i) => [$id => ['order' => $i + 1]])->all());

        // Students
        $emails = [];
        for ($i = 1; $i <= $studentCount; $i++) {
            $email = sprintf('loadtest-student-%03d@example.test', $i);
            $student = User::updateOrCreate(
                ['email' => $email],
                ['name' => sprintf('Siswa Load Test %03d', $i), 'password' => $password, 'email_verified_at' => now()]
            );
            $student->syncRoles(['student']);
            StudentProfile::firstOrCreate(['user_id' => $student->id], ['student_status' => 'active']);
            $student->enrollments()->syncWithoutDetaching([$course->id => ['enrolled_at' => now()]]);
            $emails[] = $email;
        }

        $fixtures = [
            'password' => self::PASSWORD,
            'emails' => $emails,
            'home' => route('home', [], false),
            'catalog' => route('courses', [], false),
            'course_public' => route('courses.show', $course->slug, false),
            'course_page' => route('student.courses.show', $course->slug, false),
            'lesson_content' => array_map(fn ($l) => route('student.lessons.content', $l, false), $articleLessons),
            'lesson_complete' => array_map(fn ($l) => route('student.lessons.complete', $l, false), $articleLessons),
            'quiz_start' => route('student.quiz.start', $quiz, false),
            'quiz_begin' => route('student.quiz.begin', $quiz, false),
            'race_quiz_begin' => route('student.quiz.begin', $raceQuiz, false),
            'race_lesson_id' => $articleLessons[0]->id,
            'race_quiz_id' => $raceQuiz->id,
            'quiz_id' => $quiz->id,
            'course_leaderboard' => route('student.course.leaderboard', $course, false),
            'module_leaderboard' => route('student.module.leaderboard', $module, false),
        ];

        file_put_contents(storage_path('app/loadtest-fixtures.json'), json_encode($fixtures, JSON_PRETTY_PRINT));
        $this->command?->info('Load test fixtures written to storage/app/loadtest-fixtures.json');
    }
}
