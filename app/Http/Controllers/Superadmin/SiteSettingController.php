<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    /**
     * Menampilkan form untuk mengedit pengaturan situs.
     */
    public function edit()
    {
        // Ambil baris pertama dari pengaturan (karena hanya ada satu)
        $settings = SiteSetting::first();

        return view('superadmin.settings.edit', compact('settings'));
    }

    /**
     * Memperbarui pengaturan situs di database.
     */
    public function update(Request $request)
    {
        $settings = SiteSetting::first();

        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'npwp' => 'nullable|string|max:25',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi untuk logo
            'vat_percentage' => 'required|numeric|min:0|max:100',
            'transaction_fee_fixed' => 'required|numeric|min:0',
            'transaction_fee_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Logika untuk menangani unggahan logo
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            // Simpan logo baru dan dapatkan path-nya
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        // Perbarui pengaturan di database
        $settings->update($validated);

        return back()->with('success', 'Pengaturan situs berhasil diperbarui.');
    }
}
