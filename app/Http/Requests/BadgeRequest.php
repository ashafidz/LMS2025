<?php

namespace App\Http\Requests;

use App\Models\Badge;

class BadgeRequest extends BadgeRulesRequest
{
    public function rules(): array
    {
        $isCreate = ! $this->badge();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            // Tanpa SVG: berkas SVG bisa memuat script dan disajikan dari disk publik
            'icon' => [$isCreate ? 'required' : 'nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:1024'],
            'is_active' => ['boolean'],
        ];

        return $this->validatesRules() ? array_merge($rules, $this->ruleRules()) : $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'title.required' => 'Judul badge wajib diisi.',
            'description.required' => 'Deskripsi badge wajib diisi.',
            'icon.required' => 'Pilih gambar ikon untuk badge ini.',
            'icon.mimes' => 'Ikon harus berupa gambar PNG, JPG, atau WEBP.',
            'icon.max' => 'Ukuran ikon maksimal 1 MB.',
        ]);
    }

    /**
     * Badge yang sudah dimiliki siswa tidak boleh diubah syaratnya.
     */
    public function isLocked(): bool
    {
        $badge = $this->badge();

        return $badge !== null && $badge->users()->exists();
    }

    protected function validatesRules(): bool
    {
        return ! $this->isLocked();
    }

    private function badge(): ?Badge
    {
        $badge = $this->route('badge');

        return $badge instanceof Badge ? $badge : null;
    }
}
