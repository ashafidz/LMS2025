<?php

namespace Tests\Feature\Certificates;

use App\Models\CertificateTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateTemplatePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
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

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_index_shows_empty_state_and_default_design_notice(string $role): void
    {
        $this->as($role)->get(route("$role.certificate-templates.index"))
            ->assertOk()
            ->assertSee('Template Sertifikat')
            ->assertSee('Belum ada template aktif. Sertifikat memakai desain bawaan.')
            ->assertSee('Belum ada template. Klik Buat Template Baru untuk mulai.')
            ->assertSee('Buat Template Baru');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_index_lists_versions_groups_by_design_and_marks_the_active_one(string $role): void
    {
        $admin = $this->makeAdmin(['name' => 'Admin Pembuat']);
        $old = CertificateTemplate::factory()->create(['name' => 'Desain 2026', 'created_by' => $admin->id, 'change_note' => 'catatan lama']);
        $new = CertificateTemplate::factory()->create(['name' => 'Desain 2026', 'created_by' => $admin->id, 'based_on_id' => $old->id]);
        CertificateTemplate::factory()->create(['name' => 'Desain Lain']);
        $new->update(['is_active' => true, 'activated_at' => now(), 'activated_by' => $admin->id]);

        $response = $this->as($role)->get(route("$role.certificate-templates.index"));

        $response->assertOk()
            ->assertSee('Desain 2026')
            ->assertSee('Desain Lain')
            ->assertSee('Admin Pembuat')
            ->assertSee('catatan lama')
            ->assertSee('Pratinjau PDF')
            ->assertSee('Jadikan Aktif')
            ->assertSee('Kembali ke Desain Bawaan')
            ->assertSee('Semua sertifikat yang diunduh siswa setelah ini akan memakai desain ini.')
            ->assertSee("Versi {$new->version}")
            ->assertSee('badge-success', false)
            ->assertDontSee('Belum ada template aktif');
    }

    public function test_index_never_shows_raw_json_or_internal_values(): void
    {
        CertificateTemplate::factory()->create();

        $this->as('admin')->get(route('admin.certificate-templates.index'))
            ->assertOk()
            ->assertDontSee('student_name', false)
            ->assertDontSee('"elements"', false)
            ->assertDontSee('background_path', false);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_create_page_has_all_editor_controls_in_plain_language(string $role): void
    {
        $this->as($role)->get(route("$role.certificate-templates.create"))
            ->assertOk()
            ->assertSee('Buat Template Sertifikat')
            ->assertSee('Gambar latar')
            ->assertSee('Tambahkan ke sertifikat')
            ->assertSee('Nama Siswa')
            ->assertSee('Judul Kursus')
            ->assertSee('Nama Instruktur')
            ->assertSee('Kode Sertifikat')
            ->assertSee('Tanggal Terbit')
            ->assertSee('Teks Bebas')
            ->assertSee('Jenis huruf')
            ->assertSee('Montserrat')
            ->assertSee('Great Vibes')
            ->assertSee('Kecilkan otomatis jika teks terlalu panjang')
            ->assertSee('Nama desain')
            ->assertSee('Catatan perubahan')
            ->assertSee('Pratinjau PDF')
            ->assertSee('Simpan sebagai Versi Baru')
            ->assertSee('js/certificate-template-editor.js', false);
    }

    public function test_edit_page_is_prefilled_from_the_chosen_version(): void
    {
        $template = CertificateTemplate::factory()->create(['name' => 'Desain Lama']);

        $this->as('admin')->get(route('admin.certificate-templates.edit', $template))
            ->assertOk()
            ->assertSee("Ubah Template (dari Versi {$template->version})")
            ->assertSee('value="Desain Lama"', false)
            ->assertSee('name="based_on_id" value="'.$template->id.'"', false)
            ->assertSee($template->backgroundUrl(), false);
    }

    public function test_validation_errors_are_shown_in_plain_language_and_input_is_kept(): void
    {
        $response = $this->as('admin')->from(route('admin.certificate-templates.create'))
            ->post(route('admin.certificate-templates.store'), ['name' => '', 'elements' => []]);

        $response->assertRedirect(route('admin.certificate-templates.create'));
        $this->followRedirects($response)
            ->assertSee('Nama desain wajib diisi.')
            ->assertSee('Tambahkan setidaknya satu elemen ke sertifikat.');
    }

    public function test_other_roles_cannot_open_the_pages(): void
    {
        $template = CertificateTemplate::factory()->create();

        foreach ([[$this->makeInstructor(), 'instructor'], [$this->makeStudent(), 'student']] as [$user, $role]) {
            $this->actingAs($user)->withSession(['active_role' => $role]);
            $this->get(url('/admin/certificate-templates'))->assertForbidden();
            $this->get(url('/superadmin/certificate-templates/create'))->assertForbidden();
            $this->get(url("/admin/certificate-templates/{$template->id}/edit"))->assertForbidden();
        }
    }

    public function test_activation_time_is_shown_in_the_users_timezone(): void
    {
        $admin = $this->makeAdmin(['timezone' => 'Asia/Jakarta', 'name' => 'Admin WIB']);
        $template = CertificateTemplate::factory()->create(['name' => 'Desain Waktu']);
        $template->update(['is_active' => true, 'activated_by' => $admin->id, 'activated_at' => '2026-10-24 03:00:00']);

        $this->actingAs($admin)->withSession(['active_role' => 'admin'])
            ->get(route('admin.certificate-templates.index'))
            ->assertOk()
            ->assertSee('24 Oct 2026 10:00')
            ->assertDontSee('24 Oct 2026 03:00');
    }
}
