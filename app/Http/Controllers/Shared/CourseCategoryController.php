<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseCategoryController extends Controller
{
    public function index()
    {
        $categories = CourseCategory::withCount('courses')->latest()->paginate(15);
        return view('shared-admin.course-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('shared-admin.course-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:course_categories,name|max:255',
        ]);

        CourseCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        $roleName = Auth::user()->getRoleNames()->first();
        return redirect()->route($roleName . '.course-categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(CourseCategory $courseCategory)
    {
        return view('shared-admin.course-categories.edit', compact('courseCategory'));
    }

    public function update(Request $request, CourseCategory $courseCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('course_categories')->ignore($courseCategory->id)],
        ]);

        $courseCategory->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        $roleName = Auth::user()->getRoleNames()->first();
        return redirect()->route($roleName . '.course-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, CourseCategory $courseCategory)
    {
        $validated = $request->validate([
            'new_category_id' => 'required|exists:course_categories,id',
        ]);

        // Pastikan kategori baru tidak sama dengan kategori yang akan dihapus
        if ($validated['new_category_id'] == $courseCategory->id) {
            return back()->with('error', 'Anda tidak bisa memindahkan kursus ke kategori yang sama.');
        }

        DB::transaction(function () use ($courseCategory, $validated) {
            // 1. Pindahkan semua kursus ke kategori baru
            Course::where('category_id', $courseCategory->id)->update(['category_id' => $validated['new_category_id']]);

            // 2. Hapus kategori lama
            $courseCategory->delete();
        });

        return back()->with('success', 'Kategori berhasil dihapus dan semua kursus terkait telah dipindahkan.');
    }
}
