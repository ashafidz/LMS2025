<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\LoadTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoadTestSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_test_seeder_is_idempotent_and_writes_fixtures(): void
    {
        putenv('LOADTEST_STUDENTS=3');
        $this->seed(LoadTestSeeder::class);
        $counts = [User::count(), Lesson::count(), DB::table('quiz_question')->count(), DB::table('question_options')->count()];
        $this->seed(LoadTestSeeder::class);
        putenv('LOADTEST_STUDENTS');

        $this->assertSame($counts, [User::count(), Lesson::count(), DB::table('quiz_question')->count(), DB::table('question_options')->count()]);
        $this->assertSame([4, 17, 40, 80], $counts);

        $fixtures = json_decode(file_get_contents(storage_path('app/loadtest-fixtures.json')), true);
        $this->assertCount(3, $fixtures['emails']);
        $this->assertCount(15, $fixtures['lesson_content']);

        $student = User::where('email', $fixtures['emails'][0])->first();
        $this->actingAs($student)->withSession(['active_role' => 'student'])->get($fixtures['course_page'])->assertOk();
        $this->actingAs($student)->withSession(['active_role' => 'student'])->get($fixtures['quiz_start'])->assertOk();

        @unlink(storage_path('app/loadtest-fixtures.json'));
    }
}
