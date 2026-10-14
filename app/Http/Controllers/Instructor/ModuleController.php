<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    public function index(Course $course)
    {
        // Otorisasi dihapus
        Gate::authorize('manage', $course);

        $modules = $course->modules()->with('lessons')->orderBy('order')->get();

        return view('instructor.modules.index', compact('course', 'modules'));
    }

    public function create(Course $course)
    {
        // Otorisasi dihapus
        Gate::authorize('manage', $course);

        return view('instructor.modules.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        // Otorisasi dihapus
        Gate::authorize('manage', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'points_required' => 'required|integer|min:0',
        ]);
        $lastOrder = $course->modules()->max('order') ?? 0;
        $course->modules()->create([
            'title' => $validated['title'],
            'points_required' => $validated['points_required'],
            'order' => $lastOrder + 1,
        ]);

        return redirect()->route('instructor.courses.modules.index', $course)->with('success', 'Module created successfully.');
    }

    public function edit(Module $module)
    {
        // Otorisasi dihapus
        Gate::authorize('manage', $module->course);

        return view('instructor.modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        // Otorisasi dihapus
        Gate::authorize('manage', $module->course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'points_required' => 'required|integer|min:0',
        ]);
        $module->update($validated);

        return redirect()->route('instructor.courses.modules.index', $module->course)->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module)
    {
        // Otorisasi dihapus
        Gate::authorize('manage', $module->course);

        $course = $module->course;
        $module->delete();

        return redirect()->route('instructor.courses.modules.index', $course)->with('success', 'Module deleted successfully.');
    }

    public function reorder(Request $request, Course $course)
    {
        // Otorisasi dihapus
        Gate::authorize('manage', $course);

        $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        foreach ($request->module_ids as $index => $moduleId) {
            Module::where('id', $moduleId)
                ->where('course_id', $course->id)
                ->update(['order' => $index + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Module order updated.']);
    }
}
