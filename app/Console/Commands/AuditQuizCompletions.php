<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditQuizCompletions extends Command
{
    protected $signature = 'lms:audit-quiz-completions';

    protected $description = 'Laporan (hanya baca): lesson kuis yang ditandai selesai tanpa attempt lulus';

    public function handle(): int
    {
        $rows = DB::table('lesson_user as lu')
            ->join('lessons as l', 'l.id', '=', 'lu.lesson_id')
            ->join('modules as m', 'm.id', '=', 'l.module_id')
            ->join('courses as c', 'c.id', '=', 'm.course_id')
            ->join('users as u', 'u.id', '=', 'lu.user_id')
            ->where('l.lessonable_type', Quiz::class)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('quiz_attempts as qa')
                    ->whereColumn('qa.quiz_id', 'l.lessonable_id')
                    ->whereColumn('qa.student_id', 'lu.user_id')
                    ->where('qa.status', 'passed');
            })
            ->orderBy('c.title')->orderBy('u.email')
            ->get([
                'u.id as user_id', 'u.email', 'c.id as course_id', 'c.title as course_title',
                'l.title as lesson_title', 'lu.completed_at',
            ]);

        if ($rows->isEmpty()) {
            $this->info('Tidak ada completion kuis tanpa attempt lulus.');

            return self::SUCCESS;
        }

        $certificates = DB::table('certificates')->get(['user_id', 'course_id'])
            ->map(fn ($c) => $c->user_id.'-'.$c->course_id)->flip();
        $converted = DB::table('course_user')->where('is_converted_to_diamond', true)->get(['user_id', 'course_id'])
            ->map(fn ($c) => $c->user_id.'-'.$c->course_id)->flip();

        $this->table(
            ['Email siswa', 'Kursus', 'Lesson kuis', 'Ditandai selesai', 'Punya sertifikat', 'Poin sudah dikonversi'],
            $rows->map(fn ($r) => [
                $r->email,
                $r->course_title,
                $r->lesson_title,
                $r->completed_at,
                $certificates->has($r->user_id.'-'.$r->course_id) ? 'ya' : 'tidak',
                $converted->has($r->user_id.'-'.$r->course_id) ? 'ya' : 'tidak',
            ])->all()
        );
        $this->warn('Total completion tidak sah: '.$rows->count().' (laporan saja, tidak ada data yang diubah)');

        return self::SUCCESS;
    }
}
