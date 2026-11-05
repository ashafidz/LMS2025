<?php

namespace App\Console\Commands;

use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\LessonDocument;
use App\Models\LessonVideo;
use App\Models\MonitoringLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneCourseMediaFiles extends Command
{
    protected $signature = 'files:prune-course-media {--dry-run : Tampilkan file yang akan dihapus tanpa menghapus}';

    protected $description = 'Hapus file media (dokumen, video, tugas, screenshot, thumbnail, cache sertifikat) dari kursus yang sudah dihapus atau tidak terpakai';

    /** File di disk yang tidak tercatat di DB diberi jeda 1 jam agar unggahan yang sedang berjalan tidak terhapus. */
    private const ORPHAN_MIN_AGE_SECONDS = 3600;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = time() - self::ORPHAN_MIN_AGE_SECONDS;

        $activeThumbnails = array_flip(
            Course::query()->whereNotNull('thumbnail_url')->pluck('thumbnail_url')->all()
        );
        $activeDocuments = array_flip(
            LessonDocument::query()->whereNotNull('file_path')->whereHas('lesson.module.course')->pluck('file_path')->all()
        );
        $activeVideos = array_flip(
            LessonVideo::query()->where('source_type', 'upload')->whereNotNull('video_path')->whereHas('lesson.module.course')->pluck('video_path')->all()
        );
        $activeSubmissions = array_flip(
            AssignmentSubmission::query()->whereNotNull('file_path')->whereHas('assignment.lesson.module.course')->pluck('file_path')->all()
        );
        $activeScreenshots = array_flip(
            MonitoringLog::query()->whereNotNull('screenshot_path')->whereHas('attempt.quiz.lesson.module.course')->pluck('screenshot_path')->all()
        );

        $deletedThumbnails = array_flip(
            Course::onlyTrashed()->whereNotNull('thumbnail_url')->pluck('thumbnail_url')->all()
        );
        $deletedDocuments = array_flip(
            LessonDocument::query()->whereNotNull('file_path')->whereDoesntHave('lesson.module.course')->pluck('file_path')->all()
        );
        $deletedVideos = array_flip(
            LessonVideo::query()->where('source_type', 'upload')->whereNotNull('video_path')->whereDoesntHave('lesson.module.course')->pluck('video_path')->all()
        );
        $deletedSubmissions = array_flip(
            AssignmentSubmission::query()->whereNotNull('file_path')->whereDoesntHave('assignment.lesson.module.course')->pluck('file_path')->all()
        );
        $deletedScreenshots = array_flip(
            MonitoringLog::query()->whereNotNull('screenshot_path')->whereDoesntHave('attempt.quiz.lesson.module.course')->pluck('screenshot_path')->all()
        );

        $targets = [
            ['folder' => 'thumbnails', 'disks' => ['public'], 'active' => $activeThumbnails, 'known_deleted' => $deletedThumbnails],
            ['folder' => 'lesson_documents', 'disks' => ['local', 'public'], 'active' => $activeDocuments, 'known_deleted' => $deletedDocuments],
            ['folder' => 'lesson_videos', 'disks' => ['public', 'local'], 'active' => $activeVideos, 'known_deleted' => $deletedVideos],
            ['folder' => 'assignment_submissions', 'disks' => ['local', 'public'], 'active' => $activeSubmissions, 'known_deleted' => $deletedSubmissions],
            ['folder' => 'screenshots/violations', 'disks' => ['local', 'public'], 'active' => $activeScreenshots, 'known_deleted' => $deletedScreenshots],
        ];

        $handled = [];
        $deletedCount = 0;

        foreach ($targets as $target) {
            foreach ($target['disks'] as $disk) {
                $storage = Storage::disk($disk);

                // 1. Hapus langsung path yang tercatat pada kursus terhapus / record yatim di DB
                foreach (array_keys($target['known_deleted']) as $path) {
                    $path = (string) $path;
                    $key = $disk.':'.$path;
                    if (isset($handled[$key]) || isset($target['active'][$path])) {
                        continue;
                    }
                    if ($storage->exists($path)) {
                        $handled[$key] = true;
                        $this->deleteOrReport($disk, $path, $dryRun);
                        $deletedCount++;
                    }
                }

                // 2. Pindai isi folder untuk file yatim yang sudah lebih tua dari batas aman
                if (! $storage->exists($target['folder'])) {
                    continue;
                }

                foreach ($storage->allFiles($target['folder']) as $path) {
                    $key = $disk.':'.$path;
                    if (isset($handled[$key]) || isset($target['active'][$path])) {
                        continue;
                    }

                    $isKnownDeleted = isset($target['known_deleted'][$path]);
                    if ($isKnownDeleted || $storage->lastModified($path) < $cutoff) {
                        $handled[$key] = true;
                        $this->deleteOrReport($disk, $path, $dryRun);
                        $deletedCount++;
                    }
                }
            }
        }

        // 3. Bersihkan cache PDF sertifikat untuk kursus yang sudah di-soft-delete
        $deletedCount += $this->pruneCachedCertificates($dryRun, $cutoff);

        $this->info(sprintf('%s%d file media tak terpakai.', $dryRun ? '[DRY RUN] Akan dihapus ' : 'Dihapus ', $deletedCount));

        return self::SUCCESS;
    }

    private function pruneCachedCertificates(bool $dryRun, int $cutoff): int
    {
        $storage = Storage::disk('local');
        if (! $storage->exists('certificates')) {
            return 0;
        }

        $activeCodes = Certificate::query()->whereHas('course')->pluck('certificate_code')->filter()->values()->all();
        $trashedCodes = Certificate::query()->whereDoesntHave('course')->pluck('certificate_code')->filter()->values()->all();

        $deletedCount = 0;
        foreach ($storage->files('certificates') as $path) {
            $basename = basename($path);
            if (! str_ends_with($basename, '.pdf')) {
                continue;
            }

            $matchesActive = false;
            foreach ($activeCodes as $code) {
                if (str_starts_with($basename, $code.'-')) {
                    $matchesActive = true;
                    break;
                }
            }
            if ($matchesActive) {
                continue;
            }

            $matchesTrashed = false;
            foreach ($trashedCodes as $code) {
                if (str_starts_with($basename, $code.'-')) {
                    $matchesTrashed = true;
                    break;
                }
            }

            if ($matchesTrashed || $storage->lastModified($path) < $cutoff) {
                $this->deleteOrReport('local', $path, $dryRun);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    private function deleteOrReport(string $disk, string $path, bool $dryRun): void
    {
        $this->line(sprintf('%s[%s] %s', $dryRun ? '[DRY RUN] Hapus ' : 'Hapus ', $disk, $path));
        if (! $dryRun) {
            Storage::disk($disk)->delete($path);
        }
    }
}
