<?php

namespace App\Services;

use App\Models\CertificateTemplate;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Mengelola versi template sertifikat. Versi tidak pernah ditimpa: setiap simpan membuat baris baru.
 * Tepat satu versi boleh aktif (atau tidak ada sama sekali, lalu desain bawaan dipakai).
 */
class CertificateTemplateService
{
    /** Sisi terpanjang maksimum gambar latar (A4 300 dpi), agar memori dompdf di shared hosting aman */
    public const MAX_IMAGE_SIDE = 3508;

    private const DISK = 'public';

    private const FOLDER = 'certificate-templates';

    private bool $activeLoaded = false;

    private ?CertificateTemplate $activeTemplate = null;

    /**
     * @param  array{name: string, elements: array, change_note?: ?string, orientation?: ?string}  $data
     */
    public function createVersion(array $data, ?UploadedFile $background, ?CertificateTemplate $basedOn, User $by): CertificateTemplate
    {
        if ($background === null && $basedOn === null) {
            throw new DomainException('Gambar latar wajib diunggah.');
        }

        $path = $basedOn?->background_path;
        $orientation = $data['orientation'] ?? $basedOn?->orientation ?? 'landscape';

        if ($background !== null) {
            [$path, $detected] = $this->storeBackground($background);
            $orientation = $data['orientation'] ?? $detected;
        }

        try {
            $template = DB::transaction(function () use ($data, $basedOn, $by, $path, $orientation) {
                // lockForUpdate menjaga nomor versi tetap unik jika dua admin menyimpan bersamaan
                $next = ((int) CertificateTemplate::query()->lockForUpdate()->max('version')) + 1;

                return CertificateTemplate::create([
                    'name' => $data['name'],
                    'version' => $next,
                    'based_on_id' => $basedOn?->id,
                    'background_path' => $path,
                    'orientation' => $orientation,
                    'elements' => array_values($data['elements']),
                    'change_note' => $data['change_note'] ?? null,
                    'is_active' => false,
                    'created_by' => $by->id,
                ]);
            });
        } catch (\Throwable $e) {
            // Gambar yang baru diunggah belum dipakai versi mana pun; jangan biarkan tertinggal di disk.
            // Gambar milik versi asal (tanpa unggahan baru) tidak boleh dihapus.
            if ($background !== null) {
                Storage::disk(self::DISK)->delete($path);
            }

            throw $e;
        }

        return $template;
    }

    public function activate(CertificateTemplate $template, User $by): void
    {
        DB::transaction(function () use ($template, $by) {
            CertificateTemplate::query()->where('is_active', true)->update(['is_active' => false]);

            // Update lewat query (bukan ->save()): objek model yang dipegang pemanggil bisa sudah kedaluwarsa
            // dan Eloquent hanya menulis atribut yang dianggap berubah.
            CertificateTemplate::query()->whereKey($template->getKey())->update([
                'is_active' => true,
                'activated_at' => now(),
                'activated_by' => $by->id,
            ]);
        });

        $template->refresh();

        $this->activeLoaded = false;
    }

    public function deactivateAll(User $by): void
    {
        CertificateTemplate::query()->where('is_active', true)->update(['is_active' => false]);

        $this->activeLoaded = false;
    }

    public function delete(CertificateTemplate $template): void
    {
        if ($template->is_active) {
            throw new DomainException('Versi yang sedang aktif tidak bisa dihapus.');
        }

        $path = $template->background_path;
        $template->delete();

        // Gambar dipakai bersama oleh versi turunan; hapus file hanya jika sudah tidak ada yang memakai
        if (! CertificateTemplate::query()->where('background_path', $path)->exists()) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public function active(): ?CertificateTemplate
    {
        if (! $this->activeLoaded) {
            $this->activeTemplate = CertificateTemplate::active()->first();
            $this->activeLoaded = true;
        }

        return $this->activeTemplate;
    }

    /**
     * Simpan gambar latar sementara (disk privat) untuk pratinjau draf; pemanggil wajib menghapusnya.
     *
     * @return array{0: string, 1: string} [path, orientation]
     */
    public function storeTemporaryBackground(UploadedFile $file): array
    {
        return $this->storeBackground($file, 'local', 'tmp/certificate-preview');
    }

    /**
     * Simpan gambar latar (diperkecil jika terlalu besar). Mengembalikan [path, orientation].
     *
     * @return array{0: string, 1: string}
     */
    private function storeBackground(UploadedFile $file, string $disk = self::DISK, string $folder = self::FOLDER): array
    {
        $binary = file_get_contents($file->getRealPath());
        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            throw new DomainException('Gambar latar tidak bisa dibaca. Gunakan file PNG atau JPG yang valid.');
        }

        $isPng = ($file->getMimeType() === 'image/png');

        // Foto dari HP/scanner menyimpan arah putar di tag EXIF. Browser memutarnya otomatis, GD dan dompdf tidak,
        // sehingga gambar tampil tegak di editor tetapi miring di PDF. Putar di sini lalu simpan tanpa EXIF.
        $rotated = false;
        if (! $isPng) {
            $angle = $this->exifRotationAngle($file->getRealPath());
            if ($angle !== 0 && ($turned = imagerotate($image, $angle, 0)) !== false) {
                $image = $turned;
                $rotated = true;
            }
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest > self::MAX_IMAGE_SIDE || $rotated) {
            $target = $image;
            if ($longest > self::MAX_IMAGE_SIDE) {
                $scale = self::MAX_IMAGE_SIDE / $longest;
                $newWidth = (int) round($width * $scale);
                $newHeight = (int) round($height * $scale);
                $target = imagecreatetruecolor($newWidth, $newHeight);
                if ($isPng) {
                    imagealphablending($target, false);
                    imagesavealpha($target, true);
                }
                imagecopyresampled($target, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                $width = $newWidth;
                $height = $newHeight;
            }

            ob_start();
            $isPng ? imagepng($target, null, 6) : imagejpeg($target, null, 85);
            $binary = (string) ob_get_clean();
        }

        $path = $folder.'/'.Str::uuid().($isPng ? '.png' : '.jpg');
        Storage::disk($disk)->put($path, $binary);

        return [$path, $width >= $height ? 'landscape' : 'portrait'];
    }

    /**
     * Sudut putar (untuk imagerotate, berlawanan arah jarum jam) agar JPEG tegak sesuai tag EXIF Orientation.
     * 3 = terbalik, 6 = perlu 90 derajat searah jarum jam, 8 = perlu 90 derajat berlawanan arah jarum jam.
     */
    private function exifRotationAngle(string $path): int
    {
        if (! function_exists('exif_read_data')) {
            return 0;
        }

        $orientation = (int) (@exif_read_data($path)['Orientation'] ?? 1);

        return [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
    }
}
