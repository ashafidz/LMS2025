<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Order;
use App\Models\QuestionTopic;
use App\Models\QuizAttempt;
use PHPUnit\Framework\TestCase;

class OwnershipCastTest extends TestCase
{
    public function test_order_casts_user_id_to_integer(): void
    {
        $order = new Order;
        $order->setRawAttributes(['user_id' => '42']);
        $this->assertSame(42, $order->getAttribute('user_id'));
    }

    public function test_course_casts_instructor_id_to_integer(): void
    {
        $course = new Course;
        $course->setRawAttributes(['instructor_id' => '7']);
        $this->assertSame(7, $course->getAttribute('instructor_id'));
    }

    public function test_question_topic_casts_instructor_id_to_integer(): void
    {
        $topic = new QuestionTopic;
        $topic->setRawAttributes(['instructor_id' => '15']);
        $this->assertSame(15, $topic->getAttribute('instructor_id'));
    }

    public function test_quiz_attempt_casts_student_id_to_integer(): void
    {
        $attempt = new QuizAttempt;
        $attempt->setRawAttributes(['student_id' => '99']);
        $this->assertSame(99, $attempt->getAttribute('student_id'));
    }
}
