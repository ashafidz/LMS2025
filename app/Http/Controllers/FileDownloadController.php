<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use App\Models\LessonDocument;
use App\Models\MonitoringLog;
use App\Services\LessonAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Menyajikan file privat (dokumen lesson, tugas siswa, screenshot kuis) hanya untuk yang berhak.
 * File disimpan di disk `local` (storage/app/private), tidak bisa dibuka lewat URL langsung.
 */
class FileDownloadController extends Controller
{
    public function lessonDocument(Request $request, Lesson $lesson)
    {
        abort_unless($lesson->lessonable instanceof LessonDocument && $lesson->module?->course, 404);
        abort_unless(LessonAccess::canAccessLesson($request->user(), $lesson), 403);

        return $this->serve($lesson->lessonable->file_path, $request, cacheable: true);
    }

    public function submission(Request $request, AssignmentSubmission $submission)
    {
        $user = $request->user();
        // Tugas atau lesson yang sudah dihapus: anggap file tidak ada
        $course = $submission->assignment?->lesson?->module?->course;
        abort_if($course === null, 404);

        abort_unless((int) $submission->user_id === (int) $user->id || LessonAccess::isManager($user, $course), 403);

        return $this->serve($submission->file_path, $request);
    }

    public function screenshot(Request $request, MonitoringLog $log)
    {
        $course = $log->attempt?->quiz?->lesson?->module?->course;
        abort_if($course === null, 404);

        abort_unless(LessonAccess::isManager($request->user(), $course), 403);

        return $this->serve($log->screenshot_path, $request);
    }

    /**
     * @param  bool  $cacheable  true hanya untuk materi kursus. Tugas siswa dan screenshot tidak boleh tersimpan di cache
     *                           browser, karena di komputer lab yang dipakai bergantian user berikutnya bisa membukanya.
     */
    private function serve(?string $path, Request $request, bool $cacheable = false)
    {
        abort_if(blank($path), 404);

        // 'public' hanya cadangan untuk file lama yang belum dipindah oleh files:move-private
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                $fullPath = Storage::disk($disk)->path($path);

                $response = $request->boolean('download')
                    ? response()->download($fullPath, basename($path), ['X-Content-Type-Options' => 'nosniff'])
                    : response()->file($fullPath, ['X-Content-Type-Options' => 'nosniff']);

                // BinaryFileResponse default-nya "public", jadi Cache-Control selalu diatur ulang di sini
                if ($cacheable) {
                    // Materi: cache sehari di browser agar PDF tidak diunduh ulang lewat PHP setiap lesson dibuka
                    $response->setPrivate();
                    $response->setMaxAge(86400);
                } else {
                    $response->headers->set('Cache-Control', 'no-store, private');
                }

                return $response;
            }
        }

        abort(404);
    }
}
