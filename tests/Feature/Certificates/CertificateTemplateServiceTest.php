<?php

namespace Tests\Feature\Certificates;

use App\Models\CertificateTemplate;
use App\Services\CertificateTemplateService;
use Database\Factories\CertificateTemplateFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    private CertificateTemplateService $service;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = app(CertificateTemplateService::class);
        $this->admin = $this->makeAdmin();
    }

    private function data(array $override = []): array
    {
        return array_merge([
            'name' => 'Desain Uji',
            'elements' => CertificateTemplateFactory::defaultElements(),
            'change_note' => null,
        ], $override);
    }

    public function test_version_numbers_increase_and_never_overwrite(): void
    {
        $v1 = $this->service->createVersion($this->data(), UploadedFile::fake()->image('a.png', 1600, 1131), null, $this->admin);
        $v2 = $this->service->createVersion($this->data(['change_note' => 'geser nama']), null, $v1, $this->admin);

        $this->assertSame(1, $v1->version);
        $this->assertSame(2, $v2->version);
        $this->assertSame($v1->id, $v2->based_on_id);
        $this->assertSame(2, CertificateTemplate::count());
        $this->assertSame($this->admin->id, $v2->created_by);
        $this->assertSame('geser nama', $v2->change_note);
    }

    public function test_new_version_without_upload_reuses_background_file(): void
    {
        $v1 = $this->service->createVersion($this->data(), UploadedFile::fake()->image('a.png', 1600, 1131), null, $this->admin);
        $v2 = $this->service->createVersion($this->data(), null, $v1, $this->admin);

        $this->assertSame($v1->background_path, $v2->background_path);
        $this->assertCount(1, Storage::disk('public')->allFiles('certificate-templates'));
    }

    public function test_orientation_comes_from_image_ratio(): void
    {
        $landscape = $this->service->createVersion($this->data(), UploadedFile::fake()->image('l.png', 1600, 1131), null, $this->admin);
        $portrait = $this->service->createVersion($this->data(), UploadedFile::fake()->image('p.png', 1131, 1600), null, $this->admin);

        $this->assertSame('landscape', $landscape->orientation);
        $this->assertSame('portrait', $portrait->orientation);
    }

    public function test_oversized_image_is_scaled_down_to_3508_on_longest_side(): void
    {
        $template = $this->service->createVersion($this->data(), UploadedFile::fake()->image('big.png', 4200, 3000), null, $this->admin);

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($template->background_path));
        $this->assertSame(3508, $w);
        $this->assertSame((int) round(3000 * 3508 / 4200), $h);
    }

    public function test_normal_sized_image_is_kept_as_is(): void
    {
        $template = $this->service->createVersion($this->data(), UploadedFile::fake()->image('ok.png', 2000, 1414), null, $this->admin);

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($template->background_path));
        $this->assertSame([2000, 1414], [$w, $h]);
    }

    public function test_only_one_template_is_active_after_repeated_activation(): void
    {
        $a = CertificateTemplate::factory()->create();
        $b = CertificateTemplate::factory()->create();
        $c = CertificateTemplate::factory()->create();

        $this->service->activate($a, $this->admin);
        $this->service->activate($b, $this->admin);
        $this->service->activate($b, $this->admin);
        $this->service->activate($c, $this->admin);
        $this->service->activate($a, $this->admin);

        $this->assertSame(1, CertificateTemplate::active()->count());
        $this->assertTrue($this->service->active()->is($a));
        $this->assertNotNull($a->fresh()->activated_at);
        $this->assertSame($this->admin->id, $a->fresh()->activated_by);
        $this->assertFalse($b->fresh()->is_active);
    }

    public function test_new_version_is_not_active_automatically(): void
    {
        $v1 = $this->service->createVersion($this->data(), UploadedFile::fake()->image('a.png', 1600, 1131), null, $this->admin);

        $this->assertFalse($v1->is_active);
        $this->assertNull($this->service->active());
    }

    public function test_deactivate_all_returns_to_default_design(): void
    {
        $a = CertificateTemplate::factory()->active()->create();

        $this->service->deactivateAll($this->admin);

        $this->assertNull($this->service->active());
        $this->assertFalse($a->fresh()->is_active);
    }

    public function test_active_template_cannot_be_deleted(): void
    {
        $a = CertificateTemplate::factory()->active()->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Versi yang sedang aktif tidak bisa dihapus.');
        try {
            $this->service->delete($a);
        } finally {
            $this->assertNotNull($a->fresh());
        }
    }

    public function test_deleting_keeps_shared_background_until_last_user_is_gone(): void
    {
        $v1 = $this->service->createVersion($this->data(), UploadedFile::fake()->image('a.png', 1600, 1131), null, $this->admin);
        $v2 = $this->service->createVersion($this->data(), null, $v1, $this->admin);

        $this->service->delete($v1);
        $this->assertTrue(Storage::disk('public')->exists($v2->background_path));
        $this->assertNull($v2->fresh()->based_on_id);

        $this->service->delete($v2);
        $this->assertFalse(Storage::disk('public')->exists($v2->background_path));
    }

    /** JPEG landscape yang diberi tag EXIF Orientation (seperti foto dari HP). */
    private function jpegWithOrientation(int $orientation, int $width = 1600, int $height = 1131): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 220, 255));
        // Penanda merah di pojok kiri-atas gambar asli, untuk melacak ke mana gambar diputar
        imagefilledrectangle($image, 0, 0, 150, 150, imagecolorallocate($image, 255, 0, 0));
        ob_start();
        imagejpeg($image, null, 85);
        $jpeg = (string) ob_get_clean();

        $tiff = 'II'.pack('v', 42).pack('V', 8).pack('v', 1)
            .pack('vvV', 0x0112, 3, 1).pack('vv', $orientation, 0).pack('V', 0);
        $payload = "Exif\x00\x00".$tiff;
        $app1 = "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;

        return UploadedFile::fake()->createWithContent('foto.jpg', substr($jpeg, 0, 2).$app1.substr($jpeg, 2));
    }

    private function isRedAt($template, string $corner): bool
    {
        $image = imagecreatefromstring(Storage::disk('public')->get($template->background_path));
        $x = in_array($corner, ['top-right', 'bottom-right'], true) ? imagesx($image) - 60 : 60;
        $y = in_array($corner, ['bottom-left', 'bottom-right'], true) ? imagesy($image) - 60 : 60;
        $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));

        return $color['red'] > 200 && $color['green'] < 80 && $color['blue'] < 80;
    }

    public function test_jpeg_with_exif_rotation_is_stored_upright(): void
    {
        $template = $this->service->createVersion($this->data(), $this->jpegWithOrientation(6), null, $this->admin);

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($template->background_path));
        $this->assertSame([1131, 1600], [$w, $h]);
        $this->assertSame('portrait', $template->orientation);
        // Orientation 6 = putar 90 derajat searah jarum jam: pojok kiri-atas pindah ke kanan-atas
        $this->assertTrue($this->isRedAt($template, 'top-right'));
    }

    public function test_jpeg_without_rotation_tag_is_left_alone(): void
    {
        $template = $this->service->createVersion($this->data(), $this->jpegWithOrientation(1), null, $this->admin);

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($template->background_path));
        $this->assertSame([1600, 1131], [$w, $h]);
        $this->assertSame('landscape', $template->orientation);
        $this->assertTrue($this->isRedAt($template, 'top-left'));
    }

    public function test_upside_down_jpeg_keeps_its_size_but_is_rotated(): void
    {
        $template = $this->service->createVersion($this->data(), $this->jpegWithOrientation(3), null, $this->admin);

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($template->background_path));
        $this->assertSame([1600, 1131], [$w, $h]);
        $this->assertSame('landscape', $template->orientation);
        $this->assertTrue($this->isRedAt($template, 'bottom-right'));
    }

    public function test_uploaded_background_is_removed_when_saving_the_version_fails(): void
    {
        CertificateTemplate::creating(function () {
            throw new \RuntimeException('database mati');
        });

        try {
            $this->service->createVersion($this->data(), UploadedFile::fake()->image('a.png', 1600, 1131), null, $this->admin);
            $this->fail('Exception seharusnya diteruskan ke pemanggil');
        } catch (\RuntimeException $e) {
            $this->assertSame('database mati', $e->getMessage());
        } finally {
            CertificateTemplate::flushEventListeners();
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_save_without_upload_keeps_the_shared_background_of_the_base_version(): void
    {
        $v1 = $this->service->createVersion($this->data(), UploadedFile::fake()->image('a.png', 1600, 1131), null, $this->admin);

        CertificateTemplate::creating(function () {
            throw new \RuntimeException('database mati');
        });

        try {
            $this->service->createVersion($this->data(), null, $v1, $this->admin);
        } catch (\RuntimeException) {
            // diharapkan
        } finally {
            CertificateTemplate::flushEventListeners();
        }

        Storage::disk('public')->assertExists($v1->background_path);
    }
}
