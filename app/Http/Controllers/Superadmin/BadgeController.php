<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BadgeController extends Controller
{
    /**
     * Menampilkan daftar semua badge.
     */
    public function index()
    {
        $badges = Badge::latest()->simplePaginate(10);
        return view('superadmin.badges.index', compact('badges'));
    }

    /**
     * Menampilkan form untuk membuat badge baru.
     */
    public function create()
    {
        return view('superadmin.badges.create');
    }

    /**
     * Menyimpan badge baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        // Simpan ikon ke storage
        $validated['icon_path'] = $request->file('icon')->store('badge-icons', 'public');

        Badge::create($validated);

        return redirect()->route('superadmin.badges.index')->with('success', 'Badge berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit badge.
     */
    public function edit(Badge $badge)
    {
        return view('superadmin.badges.edit', compact('badge'));
    }

    /**
     * Memperbarui badge di database.
     */
    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        // Jika ada ikon baru yang diunggah
        if ($request->hasFile('icon')) {
            // Hapus ikon lama jika ada
            if ($badge->icon_path) {
                Storage::disk('public')->delete($badge->icon_path);
            }
            // Simpan ikon baru
            $validated['icon_path'] = $request->file('icon')->store('badge-icons', 'public');
        }

        $badge->update($validated);

        return redirect()->route('superadmin.badges.index')->with('success', 'Badge berhasil diperbarui.');
    }

    /**
     * Menghapus badge dari database.
     */
    public function destroy(Badge $badge)
    {
        // Hapus ikon dari storage sebelum menghapus record
        if ($badge->icon_path) {
            Storage::disk('public')->delete($badge->icon_path);
        }

        $badge->delete();

        return back()->with('success', 'Badge berhasil dihapus.');
    }
}
