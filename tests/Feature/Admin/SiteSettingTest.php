<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'site_name' => 'Ruang Studi Test',
            'company_name' => 'PT Ruang Studi',
            'address' => 'Jl. Test No. 1',
            'phone' => '081234567890',
            'npwp' => '12.345.678.9-012.345',
            'vat_percentage' => 11,
            'transaction_fee_fixed' => 2000,
            'transaction_fee_percentage' => 1.5,
            'points_for_purchase' => 10,
            'points_for_article' => 5,
            'points_for_video' => 5,
            'points_for_document' => 5,
            'points_for_quiz' => 10,
            'points_for_assignment' => 15,
            'points_for_polling' => 2,
            'points_for_wordcloud' => 2,
            'point_to_diamond_rate' => 100,
            'email' => 'info@ruangstudi.test',
            'facebook_url' => 'https://facebook.com/ruangstudi',
            'twitter_url' => 'https://twitter.com/ruangstudi',
            'instagram_url' => 'https://instagram.com/ruangstudi',
            'youtube_url' => 'https://youtube.com/ruangstudi',
            'hero_video_url' => 'https://www.youtube.com/watch?v=sample123',
        ], $overrides);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->withSession(['active_role' => 'admin'])
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('name="hero_video_url"', false)
            ->assertSee(route('admin.settings.update'), false);
    }

    public function test_superadmin_can_view_settings_page(): void
    {
        $superadmin = $this->makeSuperadmin();

        $this->actingAs($superadmin)
            ->withSession(['active_role' => 'superadmin'])
            ->get(route('superadmin.settings.edit'))
            ->assertOk()
            ->assertSee('name="hero_video_url"', false)
            ->assertSee(route('superadmin.settings.update'), false);
    }

    public function test_student_and_instructor_cannot_access_settings(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();

        $instructor = $this->makeInstructor();
        $this->actingAs($instructor)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_update_hero_video_url_and_settings(): void
    {
        $admin = $this->makeAdmin();

        $payload = $this->validPayload([
            'hero_video_url' => 'https://www.youtube.com/watch?v=customVideoAdmin',
        ]);

        $this->actingAs($admin)
            ->withSession(['active_role' => 'admin'])
            ->put(route('admin.settings.update'), $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('site_settings', [
            'hero_video_url' => 'https://www.youtube.com/watch?v=customVideoAdmin',
            'site_name' => 'Ruang Studi Test',
        ]);
    }

    public function test_superadmin_can_update_hero_video_url_and_settings(): void
    {
        $superadmin = $this->makeSuperadmin();

        $payload = $this->validPayload([
            'hero_video_url' => 'https://www.youtube.com/watch?v=customVideoSuperadmin',
        ]);

        $this->actingAs($superadmin)
            ->withSession(['active_role' => 'superadmin'])
            ->put(route('superadmin.settings.update'), $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('site_settings', [
            'hero_video_url' => 'https://www.youtube.com/watch?v=customVideoSuperadmin',
            'site_name' => 'Ruang Studi Test',
        ]);
    }

    public function test_hero_video_url_can_be_null(): void
    {
        $admin = $this->makeAdmin();

        $payload = $this->validPayload([
            'hero_video_url' => null,
        ]);

        $this->actingAs($admin)
            ->withSession(['active_role' => 'admin'])
            ->put(route('admin.settings.update'), $payload)
            ->assertSessionHas('success');

        $setting = SiteSetting::first();
        $this->assertNotNull($setting);
        $this->assertNull($setting->hero_video_url);
    }
}
