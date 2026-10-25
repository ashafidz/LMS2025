<?php

namespace Database\Factories;

use App\Models\CertificateTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificateTemplate>
 */
class CertificateTemplateFactory extends Factory
{
    protected $model = CertificateTemplate::class;

    public function definition(): array
    {
        return [
            'name' => 'Desain '.fake()->word(),
            'version' => (CertificateTemplate::max('version') ?? 0) + 1,
            'based_on_id' => null,
            'background_path' => 'certificate-templates/'.fake()->uuid().'.png',
            'orientation' => 'landscape',
            'elements' => self::defaultElements(),
            'change_note' => null,
            'is_active' => false,
            'activated_at' => null,
        ];
    }

    /**
     * Elemen minimal yang sah: hanya Nama Siswa.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaultElements(): array
    {
        return [
            [
                'key' => 'student_name',
                'x' => 50,
                'y' => 55,
                'width' => 70,
                'align' => 'center',
                'font' => 'helvetica',
                'size' => 36,
                'color' => '#000000',
                'bold' => true,
                'italic' => false,
                'shrink_to_fit' => true,
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true, 'activated_at' => now()]);
    }
}
