<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            // Validasi baru untuk nominal poin
            'points_for_purchase' => 'required|integer|min:0',
            'points_for_article' => 'required|integer|min:0',
            'points_for_video' => 'required|integer|min:0',
            'points_for_document' => 'required|integer|min:0',
            'points_for_quiz' => 'required|integer|min:0',
            'points_for_assignment' => 'required|integer|min:0',

            // Validasi konversi poin ke diamond
            'point_to_diamond_rate' => 'required|numeric|min:0',

            'email' => 'nullable|email|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
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

    /**
     * Menampilkan daftar kursus berbayar (Uang) beserta kolom input harga.
     */
    public function bulkPriceEdit()
    {
        $courses = Course::where('payment_type', 'money')
            ->with('instructor:id,name')
            ->select('id', 'title', 'price', 'status', 'instructor_id')
            ->orderBy('title')
            ->get();

        return view('superadmin.settings.bulk-price', compact('courses'));
    }

    /**
     * Memperbarui harga satu kursus secara individual.
     */
    public function updateCoursePrice(Request $request, Course $course)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
        ], [
            'price.required' => 'Harga wajib diisi.',
            'price.numeric'  => 'Harga harus berupa angka.',
            'price.min'      => 'Harga tidak boleh negatif.',
        ]);

        if ($course->payment_type !== 'money') {
            return back()->with('error', 'Kursus ini tidak menggunakan metode pembayaran Rupiah.');
        }

        $course->update(['price' => $validated['price']]);

        return back()->with('success', 'Harga kursus "' . $course->title . '" berhasil diperbarui menjadi Rp ' . number_format($validated['price'], 0, ',', '.') . '.');
    }
}
