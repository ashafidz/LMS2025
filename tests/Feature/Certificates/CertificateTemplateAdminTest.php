<?php

namespace Tests\Feature\Certificates;

use App\Models\CertificateTemplate;
use Database\Factories\CertificateTemplateFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateTemplateAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    public static function roles(): array
    {
        return [['admin'], ['superadmin']];
    }

    private function as(string $role)
    {
        $user = $role === 'admin' ? $this->makeAdmin() : $this->makeSuperadmin();

        return $this->actingAs($user)->withSession(['active_role' => $role]);
    }

    private function payload(array $override = [], bool $withImage = true): array
    {
        $base = [
            'name' => 'Desain 2026',
            'change_note' => 'Versi pertama',
            'elements' => [
                array_merge(CertificateTemplateFactory::defaultElements()[0], ['bold' => '1', 'shrink_to_fit' => '1']),
                ['key' => 'course_title', 'x' => 50, 'y' => 40, 'width' => 70, 'align' => 'center', 'font' => 'times', 'size' => 24, 'color' => '#333333'],
                ['key' => 'issued_date', 'x' => 20, 'y' => 85, 'width' => 30, 'align' => 'left', 'font' => 'montserrat', 'size' => 14, 'color' => '#555555', 'date_format' => 'short'],
                ['key' => 'custom_text', 'x' => 50, 'y' => 20, 'width' => 60, 'align' => 'center', 'font' => 'greatvibes', 'size' => 30, 'color' => '#111111', 'text' => 'Sertifikat Kelulusan'],
            ],
        ];
        if ($withImage) {
            $base['background'] = UploadedFile::fake()->image('bg.png', 1600, 1131);
        }

        return array_replace($base, $override);
    }

    private function url(string $role, string $suffix = ''): string
    {
        return url("/{$role}/certificate-templates".$suffix);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_store_creates_a_new_version_and_it_is_not_active(string $role): void
    {
        $response = $this->as($role)->post($this->url($role), $this->payload());

        $response->assertRedirect(route("$role.certificate-templates.index"));
        $response->assertSessionHas('success');
        $template = CertificateTemplate::firstOrFail();
        $this->assertSame(1, $template->version);
        $this->assertFalse($template->is_active);
        $this->assertSame('Desain 2026', $template->name);
        $this->assertSame('landscape', $template->orientation);
        $this->assertCount(4, $template->elements);
        $this->assertTrue($template->elements[0]['bold']);
        $this->assertSame('Sertifikat Kelulusan', $template->elements[3]['text']);
        $this->assertSame('short', $template->elements[2]['date_format']);
        Storage::disk('public')->assertExists($template->background_path);
    }

    public function test_store_from_existing_version_without_new_image_reuses_background(): void
    {
        $this->as('admin')->post($this->url('admin'), $this->payload())->assertSessionHasNoErrors();
        $v1 = CertificateTemplate::firstOrFail();

        $this->as('superadmin')->post($this->url('superadmin'), $this->payload(['based_on_id' => $v1->id], withImage: false))
            ->assertSessionHasNoErrors();

        $v2 = CertificateTemplate::orderByDesc('version')->first();
        $this->assertSame(2, $v2->version);
        $this->assertSame($v1->id, $v2->based_on_id);
        $this->assertSame($v1->background_path, $v2->background_path);
        $this->assertCount(1, Storage::disk('public')->allFiles('certificate-templates'));
    }

    public function test_instructor_student_and_guest_cannot_use_the_feature(): void
    {
        $this->post(route('admin.certificate-templates.store'), $this->payload())->assertRedirect(route('login'));

        $this->actingAs($this->makeInstructor())->withSession(['active_role' => 'instructor'])
            ->post($this->url('admin'), $this->payload())->assertForbidden();
        $this->actingAs($this->makeStudent())->withSession(['active_role' => 'student'])
            ->post($this->url('superadmin'), $this->payload())->assertForbidden();
        $this->assertSame(0, CertificateTemplate::count());
    }

    public static function invalidPayloads(): array
    {
        return [
            'tanpa nama' => [['name' => ''], 'name'],
            'posisi di luar halaman' => [['elements.0.x' => 120], 'elements.0.x'],
            'lebar terlalu kecil' => [['elements.0.width' => 2], 'elements.0.width'],
            'warna salah' => [['elements.0.color' => 'merah'], 'elements.0.color'],
            'font tidak dikenal' => [['elements.0.font' => 'comicsans'], 'elements.0.font'],
            'ukuran huruf terlalu besar' => [['elements.0.size' => 200], 'elements.0.size'],
            'jenis elemen tidak dikenal' => [['elements.1.key' => 'password'], 'elements.1.key'],
            'perataan salah' => [['elements.0.align' => 'justify'], 'elements.0.align'],
            'teks bebas kosong' => [['elements.3.text' => ''], 'elements.3.text'],
            'teks bebas terlalu panjang' => [['elements.3.text' => str_repeat('a', 201)], 'elements.3.text'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPayloads')]
    public function test_invalid_input_is_rejected(array $override, string $errorKey): void
    {
        $payload = $this->payload();
        foreach ($override as $path => $value) {
            data_set($payload, $path, $value);
        }

        $this->as('admin')->post($this->url('admin'), $payload)->assertSessionHasErrors($errorKey);

        $this->assertSame(0, CertificateTemplate::count());
    }

    public function test_template_must_contain_student_name_exactly_once(): void
    {
        $withoutName = $this->payload();
        array_shift($withoutName['elements']);
        $this->as('admin')->post($this->url('admin'), $withoutName)->assertSessionHasErrors('elements');

        $twice = $this->payload();
        $twice['elements'][] = $twice['elements'][0];
        $this->as('admin')->post($this->url('admin'), $twice)->assertSessionHasErrors('elements');

        $duplicateTitle = $this->payload();
        $duplicateTitle['elements'][] = $duplicateTitle['elements'][1];
        $this->as('admin')->post($this->url('admin'), $duplicateTitle)->assertSessionHasErrors('elements');

        $this->assertSame(0, CertificateTemplate::count());
    }

    public function test_several_custom_texts_are_allowed(): void
    {
        $payload = $this->payload();
        $payload['elements'][] = ['key' => 'custom_text', 'x' => 50, 'y' => 95, 'width' => 40, 'align' => 'center', 'font' => 'helvetica', 'size' => 10, 'color' => '#000000', 'text' => 'Catatan kaki'];

        $this->as('admin')->post($this->url('admin'), $payload)->assertSessionHasNoErrors();

        $this->assertCount(5, CertificateTemplate::firstOrFail()->elements);
    }

    public function test_background_image_rules(): void
    {
        $notImage = $this->payload(['background' => UploadedFile::fake()->create('bg.pdf', 100, 'application/pdf')]);
        $this->as('admin')->post($this->url('admin'), $notImage)->assertSessionHasErrors('background');

        $tooSmall = $this->payload(['background' => UploadedFile::fake()->image('kecil.png', 500, 300)]);
        $this->as('admin')->post($this->url('admin'), $tooSmall)->assertSessionHasErrors('background');

        $tooHeavy = $this->payload(['background' => UploadedFile::fake()->image('berat.png', 1600, 1131)->size(6000)]);
        $this->as('admin')->post($this->url('admin'), $tooHeavy)->assertSessionHasErrors('background');

        $missing = $this->payload(withImage: false);
        $this->as('admin')->post($this->url('admin'), $missing)->assertSessionHasErrors('background');

        $this->assertSame(0, CertificateTemplate::count());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_activate_deactivate_and_delete_rules(string $role): void
    {
        $a = CertificateTemplate::factory()->create();
        $b = CertificateTemplate::factory()->create();

        $this->as($role)->patch($this->url($role, "/{$a->id}/activate"))->assertSessionHas('success');
        $this->as($role)->patch($this->url($role, "/{$b->id}/activate"))->assertSessionHas('success');
        $this->assertTrue($b->fresh()->is_active);
        $this->assertFalse($a->fresh()->is_active);
        $this->assertSame(1, CertificateTemplate::active()->count());

        $this->as($role)->delete($this->url($role, "/{$b->id}"))->assertSessionHas('error');
        $this->assertNotNull($b->fresh());

        $this->as($role)->delete($this->url($role, "/{$a->id}"))->assertSessionHas('success');
        $this->assertNull(CertificateTemplate::find($a->id));

        $this->as($role)->patch($this->url($role, '/deactivate'))->assertSessionHas('success');
        $this->assertSame(0, CertificateTemplate::active()->count());
    }

    public function test_draft_preview_returns_a_pdf_without_saving_anything(): void
    {
        $response = $this->as('admin')->post($this->url('admin', '/preview'), $this->payload());

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertSame(0, CertificateTemplate::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertSame([], Storage::disk('local')->allFiles('tmp'));
    }

    public function test_draft_preview_can_use_the_background_of_an_existing_version(): void
    {
        $this->as('admin')->post($this->url('admin'), $this->payload());
        $v1 = CertificateTemplate::firstOrFail();

        $response = $this->as('admin')->post($this->url('admin', '/preview'), $this->payload(['based_on_id' => $v1->id], withImage: false));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertSame(1, CertificateTemplate::count());
    }

    public function test_saved_version_can_be_previewed_as_pdf(): void
    {
        $this->as('admin')->post($this->url('admin'), $this->payload());
        $template = CertificateTemplate::firstOrFail();

        $response = $this->as('superadmin')->get($this->url('superadmin', "/{$template->id}/preview"));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_draft_preview_is_throttled(): void
    {
        $user = $this->makeAdmin();
        $statuses = [];
        for ($i = 0; $i < 21; $i++) {
            $statuses[] = $this->actingAs($user)->withSession(['active_role' => 'admin'])
                ->post($this->url('admin', '/preview'), ['name' => ''])->status();
        }

        $this->assertSame(429, end($statuses));
    }

    /** PNG kecil yang hanya berisi header berukuran palsu: getimagesize membacanya tanpa decode, tes tidak memakan memori. */
    private function fakeSizedPng(int $width, int $height): UploadedFile
    {
        $ihdr = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
        $bytes = "\x89PNG\r\n\x1a\n"
            .pack('N', 13).'IHDR'.$ihdr.pack('N', crc32('IHDR'.$ihdr))
            .pack('N', 0).'IEND'.pack('N', crc32('IEND'));

        return UploadedFile::fake()->createWithContent('besar.png', $bytes);
    }

    public function test_huge_background_images_are_rejected_before_they_can_exhaust_memory(): void
    {
        $tooLongSide = $this->payload(['background' => $this->fakeSizedPng(7000, 3000)]);
        $this->as('admin')->post($this->url('admin'), $tooLongSide)
            ->assertSessionHasErrors(['background' => 'Gambar terlalu besar. Perkecil dulu hingga sisi terpanjang maksimal 6000 piksel dan total maksimal 25 megapiksel.']);

        $tooManyPixels = $this->payload(['background' => $this->fakeSizedPng(5900, 4400)]);
        $this->as('admin')->post($this->url('admin'), $tooManyPixels)
            ->assertSessionHasErrors('background');

        $this->assertSame(0, CertificateTemplate::count());
    }

    public function test_draft_preview_also_rejects_huge_background_images(): void
    {
        $this->as('admin')->postJson($this->url('admin', '/preview'), $this->payload(['background' => $this->fakeSizedPng(7000, 3000)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('background');
    }

    public function test_draft_preview_does_not_require_a_design_name_but_saving_does(): void
    {
        $preview = $this->payload(['name' => '']);
        $response = $this->as('admin')->post($this->url('admin', '/preview'), $preview);

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));

        $this->as('admin')->post($this->url('admin'), $this->payload(['name' => '']))->assertSessionHasErrors('name');
        $this->assertSame(0, CertificateTemplate::count());
    }

    public function test_draft_preview_with_unreadable_image_returns_a_clear_message_instead_of_an_error_page(): void
    {
        // Header PNG valid (lolos validasi ukuran) tetapi isi gambarnya tidak ada, jadi tidak bisa dibuka
        $response = $this->as('admin')->post(
            $this->url('admin', '/preview'),
            $this->payload(['background' => $this->fakeSizedPng(1600, 1131)]),
            ['Accept' => 'application/json']
        );

        $response->assertStatus(422)
            ->assertJsonPath('errors.background.0', 'Gambar latar tidak bisa dibaca. Gunakan file PNG atau JPG yang valid.');
        $this->assertSame([], Storage::disk('local')->allFiles('tmp'));
    }

    public function test_draft_preview_explains_when_the_base_version_background_is_missing(): void
    {
        $this->as('admin')->post($this->url('admin'), $this->payload());
        $v1 = CertificateTemplate::firstOrFail();
        Storage::disk('public')->delete($v1->background_path);

        $this->as('admin')->post($this->url('admin', '/preview'), $this->payload(['based_on_id' => $v1->id], withImage: false), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('errors.background.0', 'Gambar latar versi ini tidak ditemukan di server. Unggah gambar latar baru.');
    }

    public function test_unexpected_draft_preview_failure_returns_json_and_is_logged(): void
    {
        $this->mock(\App\Services\CertificateRenderer::class, function ($mock) {
            $mock->shouldReceive('samplePdf')->andThrow(new \RuntimeException('dompdf rusak'));
        });
        $reported = [];
        $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->reportable(function (\RuntimeException $e) use (&$reported) {
            $reported[] = $e->getMessage();
        });

        $this->as('admin')->post($this->url('admin', '/preview'), $this->payload(), ['Accept' => 'application/json'])
            ->assertStatus(500)
            ->assertExactJson(['message' => 'Pratinjau gagal dibuat. Coba lagi.']);

        $this->assertSame(['dompdf rusak'], $reported);
        $this->assertSame([], Storage::disk('local')->allFiles('tmp'));
    }
}
