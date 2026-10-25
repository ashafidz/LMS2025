<?php

namespace App\Http\Requests;

use App\Support\CertificateFonts;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CertificateTemplateRequest extends FormRequest
{
    /** Batas dimensi gambar latar yang diterima (piksel), supaya decode di server tidak kehabisan memori */
    public const MAX_IMAGE_SIDE = 6000;

    public const MAX_IMAGE_PIXELS = 25_000_000;

    public const ELEMENT_KEYS = [
        'student_name' => 'Nama Siswa',
        'course_title' => 'Judul Kursus',
        'instructor_name' => 'Nama Instruktur',
        'certificate_code' => 'Kode Sertifikat',
        'issued_date' => 'Tanggal Terbit',
        'custom_text' => 'Teks Bebas',
    ];

    private function isDraftPreview(): bool
    {
        return $this->routeIs('*.certificate-templates.preview-draft');
    }

    public function authorize(): bool
    {
        // Akses dijaga oleh middleware role di route (admin dan superadmin)
        return true;
    }

    public function rules(): array
    {
        return [
            // Pratinjau PDF boleh dibuat sebelum desain diberi nama; menyimpan tetap wajib bernama
            'name' => [$this->isDraftPreview() ? 'nullable' : 'required', 'string', 'max:100'],
            'change_note' => ['nullable', 'string', 'max:255'],
            'orientation' => ['nullable', Rule::in(['landscape', 'portrait'])],
            'based_on_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],
            'background' => [
                'bail',
                Rule::requiredIf(fn () => ! $this->filled('based_on_id')),
                'nullable',
                'file',
                'mimes:jpeg,jpg,png',
                'max:5120',
                function (string $attribute, mixed $value, \Closure $fail) {
                    // getimagesize hanya membaca header, jadi aman untuk gambar yang sangat besar
                    $size = $value ? @getimagesize($value->getRealPath()) : false;
                    if (! $size) {
                        $fail('Gambar latar tidak bisa dibaca. Gunakan file PNG atau JPG yang valid.');
                    } elseif (max($size[0], $size[1]) < 1000) {
                        $fail('Gambar terlalu kecil. Sisi terpanjang minimal 1000 piksel agar hasil cetak tetap tajam.');
                    } elseif (max($size[0], $size[1]) > self::MAX_IMAGE_SIDE || $size[0] * $size[1] > self::MAX_IMAGE_PIXELS) {
                        // Membuka gambar sebesar ini di server memakan ratusan MB memori dan membuat halaman error
                        $fail('Gambar terlalu besar. Perkecil dulu hingga sisi terpanjang maksimal 6000 piksel dan total maksimal 25 megapiksel.');
                    }
                },
            ],
            'elements' => ['required', 'array', 'min:1', 'max:30'],
            'elements.*.key' => ['required', Rule::in(array_keys(self::ELEMENT_KEYS))],
            'elements.*.x' => ['required', 'numeric', 'between:0,100'],
            'elements.*.y' => ['required', 'numeric', 'between:0,100'],
            'elements.*.width' => ['required', 'numeric', 'between:5,100'],
            'elements.*.align' => ['required', Rule::in(['left', 'center', 'right'])],
            'elements.*.font' => ['required', Rule::in(CertificateFonts::keys())],
            'elements.*.size' => ['required', 'integer', 'between:6,96'],
            'elements.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'elements.*.bold' => ['nullable', 'boolean'],
            'elements.*.italic' => ['nullable', 'boolean'],
            'elements.*.shrink_to_fit' => ['nullable', 'boolean'],
            'elements.*.text' => ['nullable', 'string', 'max:200'],
            'elements.*.date_format' => ['nullable', Rule::in(['long', 'short'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $elements = $this->input('elements');
            if (! is_array($elements)) {
                return;
            }

            foreach ($elements as $index => $element) {
                if (($element['key'] ?? null) === 'custom_text' && trim((string) ($element['text'] ?? '')) === '') {
                    $validator->errors()->add("elements.$index.text", 'Isi Teks Bebas tidak boleh kosong.');
                }
            }

            $counts = array_count_values(array_filter(array_column($elements, 'key'), fn ($k) => is_string($k)));
            if (($counts['student_name'] ?? 0) !== 1) {
                $validator->errors()->add('elements', 'Template harus memuat tepat satu Nama Siswa.');
            }
            foreach ($counts as $key => $count) {
                if ($key !== 'custom_text' && $count > 1) {
                    $validator->errors()->add('elements', 'Elemen "'.(self::ELEMENT_KEYS[$key] ?? $key).'" hanya boleh ada satu. Hapus yang berlebih.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama desain wajib diisi.',
            'name.max' => 'Nama desain maksimal 100 karakter.',
            'background.required' => 'Gambar latar wajib diunggah.',
            'background.mimes' => 'Gambar latar harus berupa file PNG atau JPG.',
            'background.max' => 'Ukuran gambar latar maksimal 5 MB.',
            'background.file' => 'Gambar latar harus berupa file.',
            'elements.required' => 'Tambahkan setidaknya satu elemen ke sertifikat.',
            'elements.*.x.between' => 'Posisi dari kiri harus antara 0 dan 100 persen.',
            'elements.*.y.between' => 'Posisi dari atas harus antara 0 dan 100 persen.',
            'elements.*.width.between' => 'Lebar kotak teks harus antara 5 dan 100 persen.',
            'elements.*.size.between' => 'Ukuran huruf harus antara 6 dan 96.',
            'elements.*.size.integer' => 'Ukuran huruf harus berupa angka bulat.',
            'elements.*.color.regex' => 'Warna huruf tidak valid. Pilih warna dari kotak warna.',
            'elements.*.font.in' => 'Jenis huruf tidak dikenal. Pilih dari daftar.',
            'elements.*.key.in' => 'Jenis elemen tidak dikenal.',
            'elements.*.align.in' => 'Perataan teks tidak valid.',
            'elements.*.text.max' => 'Teks Bebas maksimal 200 karakter.',
        ];
    }

    /** Hanya atribut yang dikenal, dengan tipe yang rapi, siap disimpan ke kolom elements. */
    public function elementsForStorage(): array
    {
        return array_map(function (array $e) {
            $clean = [
                'key' => $e['key'],
                'x' => round((float) $e['x'], 2),
                'y' => round((float) $e['y'], 2),
                'width' => round((float) $e['width'], 2),
                'align' => $e['align'],
                'font' => $e['font'],
                'size' => (int) $e['size'],
                'color' => strtolower($e['color']),
                'bold' => filter_var($e['bold'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'italic' => filter_var($e['italic'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'shrink_to_fit' => filter_var($e['shrink_to_fit'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
            if ($e['key'] === 'custom_text') {
                $clean['text'] = trim((string) $e['text']);
            }
            if ($e['key'] === 'issued_date') {
                $clean['date_format'] = $e['date_format'] ?? 'long';
            }

            return $clean;
        }, array_values($this->input('elements')));
    }
}
