<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Question;
use PHPUnit\Framework\TestCase;

class StatusAndTypeLabelTest extends TestCase
{
    public function test_course_status_label_is_indonesian_with_a_fallback(): void
    {
        $expected = [
            'draft' => 'Draf',
            'pending_review' => 'Menunggu tinjauan',
            'published' => 'Terbit',
            'rejected' => 'Ditolak',
            'private' => 'Privat',
            'in_review_2' => 'In review 2',
        ];

        foreach ($expected as $status => $label) {
            $this->assertSame($label, (new Course(['status' => $status]))->status_label);
        }
    }

    public function test_question_type_label_is_indonesian_with_a_fallback(): void
    {
        $expected = [
            'multiple_choice_single' => 'Pilihan ganda (satu jawaban)',
            'multiple_choice_multiple' => 'Pilihan ganda (banyak jawaban)',
            'true_false' => 'Benar/Salah',
            'drag_and_drop' => 'Isian (drag and drop)',
            'new_type' => 'New type',
        ];

        foreach ($expected as $type => $label) {
            $this->assertSame($label, (new Question(['question_type' => $type]))->type_label);
        }
    }
}
