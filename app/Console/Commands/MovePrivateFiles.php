<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MovePrivateFiles extends Command
{
    protected $signature = 'files:move-private {--dry-run : Hanya tampilkan apa yang akan dipindah}';

    protected $description = 'Pindahkan dokumen lesson, file tugas siswa, dan screenshot kuis dari disk publik ke disk privat';

    /** tabel => kolom path */
    private const TARGETS = [
        'assignment_submissions' => 'file_path',
        'lesson_documents' => 'file_path',
        'monitoring_logs' => 'screenshot_path',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = $skipped = $failed = 0;

        foreach (self::TARGETS as $table => $column) {
            DB::table($table)->whereNotNull($column)->orderBy('id')->chunkById(100, function ($rows) use ($table, $column, $dryRun, $public, $private, &$moved, &$skipped, &$failed) {
                foreach ($rows as $row) {
                    $path = $row->{$column};

                    // Sudah di disk privat atau file tidak ada di disk publik: tidak ada yang dipindah
                    if ($private->exists($path) && ! $public->exists($path)) {
                        $skipped++;

                        continue;
                    }
                    if (! $public->exists($path)) {
                        $this->warn("[$table#{$row->id}] file tidak ditemukan: $path");
                        $skipped++;

                        continue;
                    }

                    if ($dryRun) {
                        $this->line("[$table#{$row->id}] akan dipindah: $path");
                        $moved++;

                        continue;
                    }

                    $private->put($path, $public->get($path));

                    if ($private->exists($path) && $private->size($path) === $public->size($path)) {
                        $public->delete($path);
                        $moved++;
                    } else {
                        $this->error("[$table#{$row->id}] gagal memindah: $path");
                        $failed++;
                    }
                }
            });
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Dipindah: $moved, dilewati: $skipped, gagal: $failed");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
