<?php

namespace Tests\Feature\Certificates;

use App\Models\CertificateTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CertificateTemplateModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('certificate_templates', [
            'id', 'name', 'version', 'based_on_id', 'background_path', 'orientation', 'elements', 'change_note',
            'is_active', 'activated_at', 'created_by', 'activated_by', 'created_at', 'updated_at',
        ]));
    }

    public function test_casts_and_factory(): void
    {
        $template = CertificateTemplate::factory()->create();

        $this->assertIsArray($template->fresh()->elements);
        $this->assertSame('student_name', $template->fresh()->elements[0]['key']);
        $this->assertFalse($template->fresh()->is_active);
        $this->assertSame(1, CertificateTemplate::query()->count());
    }

    public function test_active_scope_and_relations(): void
    {
        $user = $this->makeAdmin();
        $base = CertificateTemplate::factory()->create(['created_by' => $user->id]);
        $active = CertificateTemplate::factory()->create([
            'based_on_id' => $base->id,
            'is_active' => true,
            'activated_by' => $user->id,
            'activated_at' => now(),
        ]);

        $this->assertTrue(CertificateTemplate::active()->first()->is($active));
        $this->assertTrue($active->basedOn->is($base));
        $this->assertTrue($base->creator->is($user));
        $this->assertTrue($active->activator->is($user));
    }

    public function test_migration_can_roll_back(): void
    {
        Artisan::call('migrate:rollback', ['--path' => 'database/migrations/2026_10_24_200000_create_certificate_templates_table.php']);
        $this->assertFalse(Schema::hasTable('certificate_templates'));
        Artisan::call('migrate');
        $this->assertTrue(Schema::hasTable('certificate_templates'));
    }
}
