<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\LessonDocument;
use App\Models\LessonVideo;
use App\Models\Module;
use App\Models\MonitoringLog;
use App\Models\Quiz;
use Illuminate\Support\Facades\Storage;

class CourseMediaCleanupService
{
    /**
     * Hapus seluruh file media fisik milik kursus (thumbnail, dokumen PDF, video upload,
     * file submission tugas, screenshot pengawasan kuis, dan cache PDF sertifikat)
     * tanpa menghapus record database yang diperlukan untuk riwayat/soft-delete.
     */
    public function cleanupCourseMedia(Course $course): int
    {
        $deletedCount = 0;

        if ($this->deleteThumbnailIfUnused($course->thumbnail_url, $course->id)) {
            $deletedCount++;
        }

        $modules = $course->modules()->with('lessons.lessonable')->get();
        foreach ($modules as $module) {
            foreach ($module->lessons as $lesson) {
                $deletedCount += $this->cleanupLessonMedia($lesson, $course->id);
            }
        }

        $deletedCount += $this->deleteCourseCachedCertificates($course);

        return $deletedCount;
    }

    /**
     * Hapus seluruh file media dan model lessonable dari setiap pelajaran di dalam modul.
     */
    public function cleanupModuleAndDeleteLessonables(Module $module): int
    {
        $deletedCount = 0;
        $lessons = $module->lessons()->with('lessonable')->get();

        foreach ($lessons as $lesson) {
            $deletedCount += $this->cleanupLessonAndDeleteLessonable($lesson);
        }

        return $deletedCount;
    }

    /**
     * Hapus seluruh file media milik satu pelajaran lalu hapus model lessonable-nya.
     */
    public function cleanupLessonAndDeleteLessonable(Lesson $lesson): int
    {
        $deletedCount = $this->cleanupLessonMedia($lesson);

        if ($lesson->lessonable) {
            $lesson->lessonable->delete();
        }

        return $deletedCount;
    }

    /**
     * Hapus file media fisik milik satu pelajaran (tanpa menghapus record di database).
     */
    public function cleanupLessonMedia(Lesson $lesson, ?int $excludeCourseId = null): int
    {
        $lessonable = $lesson->lessonable;
        if (! $lessonable) {
            return 0;
        }

        if ($lessonable instanceof LessonDocument) {
            return $this->deleteDocumentPathIfUnused($lessonable->file_path, $lessonable->id, $excludeCourseId) ? 1 : 0;
        }

        if ($lessonable instanceof LessonVideo) {
            if ($lessonable->source_type === 'upload') {
                return $this->deleteVideoPathIfUnused($lessonable->video_path, $lessonable->id, $excludeCourseId) ? 1 : 0;
            }

            return 0;
        }

        if ($lessonable instanceof LessonAssignment) {
            return $this->deleteAssignmentSubmissionFiles($lessonable);
        }

        if ($lessonable instanceof Quiz) {
            return $this->deleteQuizMonitoringScreenshots($lessonable);
        }

        return 0;
    }

    /**
     * Hapus file thumbnail kursus jika tidak sedang dipakai oleh kursus aktif lain.
     */
    public function deleteThumbnailIfUnused(?string $path, ?int $excludeCourseId = null): bool
    {
        if (blank($path) || $this->isThumbnailUsedByActiveCourse($path, $excludeCourseId)) {
            return false;
        }

        return $this->deletePathFromDisks($path, ['public']);
    }

    /**
     * Hapus file dokumen PDF jika tidak sedang dipakai oleh LessonDocument di kursus aktif lain.
     */
    public function deleteDocumentPathIfUnused(?string $path, ?int $excludeDocumentId = null, ?int $excludeCourseId = null): bool
    {
        if (blank($path) || $this->isDocumentPathUsedByActiveCourse($path, $excludeDocumentId, $excludeCourseId)) {
            return false;
        }

        return $this->deletePathFromDisks($path, ['local', 'public']);
    }

    /**
     * Hapus file video upload jika tidak sedang dipakai oleh LessonVideo di kursus aktif lain.
     */
    public function deleteVideoPathIfUnused(?string $path, ?int $excludeVideoId = null, ?int $excludeCourseId = null): bool
    {
        if (blank($path) || $this->isVideoPathUsedByActiveCourse($path, $excludeVideoId, $excludeCourseId)) {
            return false;
        }

        return $this->deletePathFromDisks($path, ['public', 'local']);
    }

    /**
     * Hapus semua file pengumpulan tugas untuk satu LessonAssignment.
     */
    public function deleteAssignmentSubmissionFiles(LessonAssignment $assignment): int
    {
        $deletedCount = 0;
        $paths = $assignment->submissions()->whereNotNull('file_path')->pluck('file_path');

        foreach ($paths as $path) {
            if ($this->deletePathFromDisks($path, ['local', 'public'])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Hapus semua file screenshot pengawasan kuis untuk satu Quiz.
     */
    public function deleteQuizMonitoringScreenshots(Quiz $quiz): int
    {
        $attemptIds = $quiz->attempts()->pluck('id');

        return $this->deleteAttemptMonitoringScreenshots($attemptIds);
    }

    /**
     * Hapus semua file screenshot pengawasan kuis untuk daftar attempt_id tertentu.
     *
     * @param  iterable<int>  $attemptIds
     */
    public function deleteAttemptMonitoringScreenshots(iterable $attemptIds): int
    {
        $ids = collect($attemptIds)->filter()->values()->all();
        if ($ids === []) {
            return 0;
        }

        $deletedCount = 0;
        $paths = MonitoringLog::query()
            ->whereIn('attempt_id', $ids)
            ->whereNotNull('screenshot_path')
            ->pluck('screenshot_path');

        foreach ($paths as $path) {
            if ($this->deletePathFromDisks($path, ['local', 'public'])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Hapus file cache PDF sertifikat untuk kursus tertentu dari disk local.
     */
    public function deleteCourseCachedCertificates(Course $course): int
    {
        $codes = $course->certificates()->pluck('certificate_code')->filter()->values()->all();
        if ($codes === []) {
            return 0;
        }

        $storage = Storage::disk('local');
        if (! $storage->exists('certificates')) {
            return 0;
        }

        $deletedCount = 0;
        foreach ($storage->files('certificates') as $file) {
            $basename = basename($file);
            foreach ($codes as $code) {
                if (str_starts_with($basename, $code.'-') && str_ends_with($basename, '.pdf')) {
                    if ($storage->delete($file)) {
                        $deletedCount++;
                    }
                    break;
                }
            }
        }

        return $deletedCount;
    }

    public function isThumbnailUsedByActiveCourse(string $path, ?int $excludeCourseId = null): bool
    {
        return Course::query()
            ->where('thumbnail_url', $path)
            ->when($excludeCourseId !== null, fn ($q) => $q->where('id', '!=', $excludeCourseId))
            ->exists();
    }

    public function isDocumentPathUsedByActiveCourse(string $path, ?int $excludeDocumentId = null, ?int $excludeCourseId = null): bool
    {
        return LessonDocument::query()
            ->where('file_path', $path)
            ->when($excludeDocumentId !== null, fn ($q) => $q->where('id', '!=', $excludeDocumentId))
            ->whereHas('lesson.module.course', function ($q) use ($excludeCourseId) {
                if ($excludeCourseId !== null) {
                    $q->where('courses.id', '!=', $excludeCourseId);
                }
            })
            ->exists();
    }

    public function isVideoPathUsedByActiveCourse(string $path, ?int $excludeVideoId = null, ?int $excludeCourseId = null): bool
    {
        return LessonVideo::query()
            ->where('source_type', 'upload')
            ->where('video_path', $path)
            ->when($excludeVideoId !== null, fn ($q) => $q->where('id', '!=', $excludeVideoId))
            ->whereHas('lesson.module.course', function ($q) use ($excludeCourseId) {
                if ($excludeCourseId !== null) {
                    $q->where('courses.id', '!=', $excludeCourseId);
                }
            })
            ->exists();
    }

    /**
     * @param  array<int, string>  $disks
     */
    public function deletePathFromDisks(?string $path, array $disks = ['local', 'public']): bool
    {
        if (blank($path)) {
            return false;
        }

        $deleted = false;
        foreach ($disks as $disk) {
            $storage = Storage::disk($disk);
            if ($storage->exists($path)) {
                $storage->delete($path);
                $deleted = true;
            }
        }

        return $deleted;
    }
}
