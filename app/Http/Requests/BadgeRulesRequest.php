<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Support\Badges\BadgeMetrics;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi daftar syarat badge. Otorisasi dijaga middleware role di route (admin atau superadmin).
 */
class BadgeRulesRequest extends FormRequest
{
    public const MAX_RULES = 3;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->ruleRules();
    }

    /**
     * @return array<string, mixed>
     */
    protected function ruleRules(): array
    {
        return [
            'rules' => ['required', 'array', 'min:1', 'max:'.self::MAX_RULES],
            'rules.*.metric' => ['required', Rule::in(BadgeMetrics::keys())],
            'rules.*.target' => ['required', 'integer', 'min:1', 'max:100000'],
            'rules.*.scope' => ['required', Rule::in([BadgeMetrics::SCOPE_ALL, BadgeMetrics::SCOPE_COURSE, BadgeMetrics::SCOPE_CATEGORY])],
            'rules.*.scope_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'rules.required' => 'Tambahkan minimal satu syarat untuk badge ini.',
            'rules.min' => 'Tambahkan minimal satu syarat untuk badge ini.',
            'rules.max' => 'Satu badge paling banyak punya '.self::MAX_RULES.' syarat.',
            'rules.*.metric.required' => 'Pilih ukuran untuk setiap syarat.',
            'rules.*.metric.in' => 'Ukuran syarat tidak dikenal. Pilih dari daftar yang tersedia.',
            'rules.*.target.required' => 'Isi angka target untuk setiap syarat.',
            'rules.*.target.integer' => 'Angka target harus berupa bilangan bulat.',
            'rules.*.target.min' => 'Angka target minimal 1.',
            'rules.*.target.max' => 'Angka target terlalu besar (maksimal 100.000).',
            'rules.*.scope.required' => 'Pilih cakupan untuk setiap syarat.',
            'rules.*.scope.in' => 'Cakupan syarat tidak dikenal.',
        ];
    }

    /**
     * Pemeriksaan yang bergantung pada pasangan ukuran dan cakupan.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            if (! $this->validatesRules()) {
                return;
            }

            foreach ((array) $this->input('rules', []) as $i => $rule) {
                $metric = $rule['metric'] ?? null;
                $scope = $rule['scope'] ?? BadgeMetrics::SCOPE_ALL;

                if (! in_array($metric, BadgeMetrics::keys(), true) || $validator->errors()->has("rules.$i.scope")) {
                    continue;
                }

                if (! in_array($scope, BadgeMetrics::scopesFor($metric), true)) {
                    $validator->errors()->add("rules.$i.scope", 'Cakupan ini tidak tersedia untuk ukuran "'.BadgeMetrics::label($metric).'".');

                    continue;
                }

                if ($scope === BadgeMetrics::SCOPE_ALL) {
                    continue;
                }

                $id = $rule['scope_id'] ?? null;
                $exists = $id !== null && $id !== '' && match ($scope) {
                    BadgeMetrics::SCOPE_COURSE => Course::whereKey($id)->exists(),
                    BadgeMetrics::SCOPE_CATEGORY => CourseCategory::whereKey($id)->exists(),
                    default => false,
                };

                if (! $exists) {
                    $validator->errors()->add(
                        "rules.$i.scope_id",
                        $scope === BadgeMetrics::SCOPE_COURSE ? 'Pilih kursus untuk syarat ini.' : 'Pilih kategori untuk syarat ini.'
                    );
                }
            }
        }];
    }

    /**
     * Syarat yang sudah dirapikan, siap disimpan.
     *
     * @return array<int, array{metric: string, target: int, scope: string, scope_id: ?int, position: int}>
     */
    public function normalizedRules(): array
    {
        return collect($this->input('rules', []))
            ->values()
            ->map(fn (array $rule, int $position) => [
                'metric' => $rule['metric'],
                'target' => (int) $rule['target'],
                'scope' => $rule['scope'],
                'scope_id' => $rule['scope'] === BadgeMetrics::SCOPE_ALL ? null : (int) $rule['scope_id'],
                'position' => $position,
            ])
            ->all();
    }

    /**
     * Subkelas bisa mematikan validasi syarat (badge terkunci).
     */
    protected function validatesRules(): bool
    {
        return true;
    }
}
