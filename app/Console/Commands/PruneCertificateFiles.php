<?php

namespace App\Console\Commands;

use App\Models\CertificateTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneCertificateFiles extends Command
{
    protected $signature = 'certificates:prune-files {--dry-run : Tampilkan file yang akan dihapus tanpa menghapus}';

    protected $description = 'Hapus gambar latar sertifikat yang tidak dipakai versi mana pun dan sisa file pratinjau';

    /** Gambar latar yang baru diunggah bisa belum tercatat di database; beri jeda sebelum dianggap tertinggal. */
    private const ORPHAN_MIN_AGE_SECONDS = 86400;

    private const PREVIEW_MIN_AGE_SECONDS = 3600;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $used = array_flip(CertificateTemplate::query()->pluck('background_path')->all());

        $orphans = $this->oldFiles('public', 'certificate-templates', self::ORPHAN_MIN_AGE_SECONDS, fn (string $path) => ! isset($used[$path]));
        $previews = $this->oldFiles('local', 'tmp/certificate-preview', self::PREVIEW_MIN_AGE_SECONDS);

        foreach ([['public', $orphans], ['local', $previews]] as [$disk, $paths]) {
            foreach ($paths as $path) {
                $this->line(($dryRun ? '[DRY RUN] ' : '').'Hapus '.$path);
                if (! $dryRun) {
                    Storage::disk($disk)->delete($path);
                }
            }
        }

        $this->info(sprintf('%s%d gambar latar tak terpakai dan %d sisa pratinjau.', $dryRun ? '[DRY RUN] ' : 'Dihapus ', count($orphans), count($previews)));

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function oldFiles(string $disk, string $folder, int $minAgeSeconds, ?callable $filter = null): array
    {
        $storage = Storage::disk($disk);
        $cutoff = time() - $minAgeSeconds;

        return array_values(array_filter(
            $storage->allFiles($folder),
            fn (string $path) => $storage->lastModified($path) < $cutoff && ($filter === null || $filter($path))
        ));
    }
}
