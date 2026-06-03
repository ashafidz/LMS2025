<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ProfilingComponent;
use App\Models\ProfilingDimension;
use App\Models\ProfilingQuestion;
use Illuminate\Http\Request;

class ProfilingQuestionController extends Controller
{
    public function storeDimension(Request $request, ProfilingComponent $component)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
        ]);

        $component->dimensions()->create($validated);
        return back()->with('success', 'Dimensi berhasil ditambahkan.');
    }

    public function updateDimension(Request $request, ProfilingDimension $dimension)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
        ]);

        $dimension->update($validated);
        return back()->with('success', 'Dimensi berhasil diupdate.');
    }

    public function destroyDimension(ProfilingDimension $dimension)
    {
        if ($dimension->questions()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus dimensi yang memiliki soal.');
        }

        $dimension->delete();
        return back()->with('success', 'Dimensi berhasil dihapus.');
    }

    public function storeQuestion(Request $request, ProfilingComponent $component)
    {
        $validated = $request->validate([
            'dimension_id' => 'required|exists:profiling_dimensions,id',
            'question_text' => 'required|string',
            'order' => 'required|integer',
        ]);

        $component->questions()->create($validated);
        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, ProfilingQuestion $question)
    {
        $validated = $request->validate([
            'dimension_id' => 'required|exists:profiling_dimensions,id',
            'question_text' => 'required|string',
            'order' => 'required|integer',
        ]);

        $question->update($validated);
        return back()->with('success', 'Soal berhasil diupdate.');
    }

    public function destroyQuestion(ProfilingQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function toggleQuestion(ProfilingQuestion $question)
    {
        $question->update(['is_active' => !$question->is_active]);
        return back()->with('success', 'Status soal berhasil diubah.');
    }
}
