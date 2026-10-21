<?php

// Prints database counts after scripts/loadtest/race.js (run via race.sh with the load test environment).
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$fx = json_decode(file_get_contents(storage_path('app/loadtest-fixtures.json')), true);
$user = User::where('email', $fx['emails'][0])->firstOrFail();
$mainAttempts = QuizAttempt::where('quiz_id', $fx['quiz_id'])->where('student_id', $user->id);

$rows = [
    'A point rows for the raced lesson' => [1, DB::table('point_histories')->where('user_id', $user->id)->where('lesson_id', $fx['race_lesson_id'])->count()],
    'A course_user rows' => [1, DB::table('course_user')->where('user_id', $user->id)->count()],
    'B attempts on the race quiz (max_attempts 1)' => [1, QuizAttempt::where('quiz_id', $fx['race_quiz_id'])->where('student_id', $user->id)->count()],
    'C submitted attempts on the main quiz' => [1, (clone $mainAttempts)->where('status', '!=', 'in_progress')->count()],
    'C stored answers (20 questions)' => [20, DB::table('student_answers')->whereIn('attempt_id', (clone $mainAttempts)->pluck('id'))->count()],
];

echo PHP_EOL.str_pad('check', 48).str_pad('expected', 10).'actual'.PHP_EOL;
foreach ($rows as $label => [$expected, $actual]) {
    echo str_pad($label, 48).str_pad($expected, 10).$actual.($expected === $actual ? '' : '   <-- WRONG').PHP_EOL;
}
