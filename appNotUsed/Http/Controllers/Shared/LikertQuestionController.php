<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\LikertQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LikertQuestionController extends Controller
{
    /**
     * Menampilkan daftar semua pertanyaan skala Likert.
     */
    public function index()
    {
        $questions = LikertQuestion::latest()->paginate(15);
        return view('shared-admin.likert-questions.index', compact('questions'));
    }

    /**
     * Menampilkan form untuk membuat pertanyaan baru.
     */
    public function create()
    {
        return view('shared-admin.likert-questions.create');
    }

    /**
     * Menyimpan pertanyaan baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            // DIUBAH: Tambahkan 'platform' ke dalam aturan validasi
            'category' => ['required', Rule::in(['course', 'instructor', 'platform'])],
            'is_active' => 'boolean',
        ]);

        LikertQuestion::create($validated);
        $roleName = Auth::user()->getRoleNames()->first();

        return redirect()->route($roleName . '.likert-questions.index')->with('success', 'Pertanyaan berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit pertanyaan.
     */
    public function edit(LikertQuestion $likertQuestion)
    {
        return view('shared-admin.likert-questions.edit', compact('likertQuestion'));
    }

    /**
     * Memperbarui pertanyaan di database.
     */
    public function update(Request $request, LikertQuestion $likertQuestion)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            // DIUBAH: Tambahkan 'platform' ke dalam aturan validasi
            'category' => ['required', Rule::in(['course', 'instructor', 'platform'])],
            'is_active' => 'boolean',
        ]);

        $likertQuestion->update($validated);
        $roleName = Auth::user()->getRoleNames()->first();

        return redirect()->route($roleName . '.likert-questions.index')->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    /**
     * Menghapus pertanyaan dari database.
     */
    public function destroy(LikertQuestion $likertQuestion)
    {
        $likertQuestion->delete();
        return back()->with('success', 'Pertanyaan berhasil dihapus.');
    }
}
