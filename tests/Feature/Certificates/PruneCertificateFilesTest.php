<?php

namespace Tests\Feature\Certificates;

use App\Models\CertificateTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Gambar latar yang tertinggal (proses mati di antara simpan file dan simpan database) dan sisa file pratinjau
 * dibersihkan oleh perintah harian, tanpa menyentuh gambar yang masih dipakai.
 */
class PruneCertificateFilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    private function putFile(string $disk, string $path, int $ageSeconds): void
    {
        Storage::disk($disk)->put($path, 'x');
        touch(Storage::disk($disk)->path($path), time() - $ageSeconds);
    }

    public function test_removes_old_unreferenced_backgrounds_and_old_preview_files_only(): void
    {
        $this->putFile('public', 'certificate-templates/dipakai.png', 3 * 86400);
        CertificateTemplate::factory()->create(['background_path' => 'certificate-templates/dipakai.png']);
        $this->putFile('public', 'certificate-templates/yatim-lama.png', 3 * 86400);
        $this->putFile('public', 'certificate-templates/yatim-baru.png', 600);
        $this->putFile('public', 'badge-icons/lain.png', 3 * 86400);
        $this->putFile('local', 'tmp/certificate-preview/sisa-lama.png', 2 * 3600);
        $this->putFile('local', 'tmp/certificate-preview/sedang-dipakai.png', 60);

        $this->artisan('certificates:prune-files')->assertExitCode(0);

        Storage::disk('public')->assertExists('certificate-templates/dipakai.png');
        Storage::disk('public')->assertMissing('certificate-templates/yatim-lama.png');
        Storage::disk('public')->assertExists('certificate-templates/yatim-baru.png');
        Storage::disk('public')->assertExists('badge-icons/lain.png');
        Storage::disk('local')->assertMissing('tmp/certificate-preview/sisa-lama.png');
        Storage::disk('local')->assertExists('tmp/certificate-preview/sedang-dipakai.png');
    }

    public function test_dry_run_reports_without_deleting(): void
    {
        $this->putFile('public', 'certificate-templates/yatim-lama.png', 3 * 86400);

        $this->artisan('certificates:prune-files', ['--dry-run' => true])
            ->expectsOutputToContain('certificate-templates/yatim-lama.png')
            ->assertExitCode(0);

        Storage::disk('public')->assertExists('certificate-templates/yatim-lama.png');
    }

    public function test_is_scheduled_daily(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command, 'certificates:prune-files'));

        $this->assertCount(1, $events);
        $this->assertSame('30 2 * * *', $events->first()->expression);
    }
}
