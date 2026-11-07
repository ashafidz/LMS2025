<?php

namespace Tests\Feature\Ui;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class FlashToastTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::create([
            'site_name' => 'ruangstudi.id',
            'points_for_purchase' => 100,
            'vat_percentage' => 0,
            'transaction_fee_fixed' => 0,
            'transaction_fee_percentage' => 0,
        ]);
        cache()->forget('site_settings');
    }

    private function instructorGet(string $url, array $flash = [])
    {
        return $this->actingAs($this->makeInstructor())
            ->withSession(array_merge(['active_role' => 'instructor'], $flash))
            ->get($url);
    }

    public function test_success_flash_becomes_a_toast_and_not_an_inline_alert(): void
    {
        $this->instructorGet(route('instructor.dashboard'), ['success' => 'Kursus berhasil disimpan.'])
            ->assertOk()
            ->assertSee('rsFlashQueue', false)
            ->assertSee('Kursus berhasil disimpan.', false)
            ->assertSee('\\u0022icon\\u0022:\\u0022success\\u0022', false)
            ->assertDontSee('alert alert-success', false);
    }

    public function test_info_flash_on_a_public_page_uses_the_info_icon(): void
    {
        $this->withSession(['info' => 'Info uji'])
            ->get('/')
            ->assertOk()
            ->assertSee('rsToast', false)
            ->assertSee('Info uji', false)
            ->assertSee('\\u0022icon\\u0022:\\u0022info\\u0022', false);
    }

    public function test_warning_flash_uses_the_warning_icon(): void
    {
        $this->instructorGet(route('instructor.dashboard'), ['warning' => 'Hati-hati'])
            ->assertOk()
            ->assertSee('\\u0022icon\\u0022:\\u0022warning\\u0022', false);
    }

    public function test_page_without_flash_has_no_toast_call(): void
    {
        $this->instructorGet(route('instructor.dashboard'))
            ->assertOk()
            ->assertDontSee('rsFlashQueue', false);

        $this->get('/')->assertOk()->assertDontSee('rsFlashQueue', false);
    }

    public function test_flash_text_is_escaped_as_data(): void
    {
        $this->instructorGet(route('instructor.dashboard'), ['success' => '<img src=x onerror=alert(1)>'])
            ->assertOk()
            ->assertDontSee('<img src=x', false);
    }

    public function test_course_list_no_longer_prints_the_success_alert(): void
    {
        $this->instructorGet(route('instructor.courses.index'), ['success' => 'Kursus dihapus.'])
            ->assertOk()
            ->assertSee('rsFlashQueue', false)
            ->assertDontSee('alert alert-success', false);
    }
}
