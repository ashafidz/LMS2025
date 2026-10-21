<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\InstructorProfile;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Order;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseZeroFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_all_core_models_using_factories(): void
    {
        $category = CourseCategory::factory()->create();
        $this->assertDatabaseHas('course_categories', ['id' => $category->id]);

        $instructorUser = User::factory()->create();
        $instructorProfile = InstructorProfile::factory()->create(['user_id' => $instructorUser->id]);
        $this->assertDatabaseHas('instructor_profiles', ['id' => $instructorProfile->id]);

        $studentUser = User::factory()->create();
        $studentProfile = StudentProfile::factory()->create(['user_id' => $studentUser->id]);
        $this->assertDatabaseHas('student_profiles', ['id' => $studentProfile->id]);

        $course = Course::factory()->create([
            'instructor_id' => $instructorUser->id,
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseHas('courses', ['id' => $course->id]);

        $module = Module::factory()->create(['course_id' => $course->id]);
        $this->assertDatabaseHas('modules', ['id' => $module->id]);

        $quiz = Quiz::factory()->create();
        $quizLesson = Lesson::factory()->forQuiz($quiz)->create(['module_id' => $module->id]);
        $this->assertDatabaseHas('lessons', ['id' => $quizLesson->id, 'lessonable_type' => Quiz::class]);

        $attempt = QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'student_id' => $studentUser->id,
        ]);
        $this->assertDatabaseHas('quiz_attempts', ['id' => $attempt->id]);

        $coupon = Coupon::factory()->create();
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);

        $order = Order::factory()->create([
            'user_id' => $studentUser->id,
            'coupon_id' => $coupon->id,
        ]);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertNotNull($order->order_code);
    }
}
