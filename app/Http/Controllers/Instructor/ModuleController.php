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

        // Modules are managed inside the course list now; old links and bookmarks land there with this course open.
        return redirect()->to($this->courseListUrl($course));
    }

    /**
     * The course list with this course's module panel open and scrolled into view.
     */
    private function courseListUrl(Course $course): string
    {
        return route('instructor.courses.index', ['open' => $course->getRouteKey()]).'#course-'.$course->getRouteKey();
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

        return redirect()->to($this->courseListUrl($course))->with('success', 'Modul berhasil dibuat.');
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

        return redirect()->to($this->courseListUrl($module->course))->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroy(Module $module)
    {
        // Otorisasi dihapus
        Gate::authorize('manage', $module->course);

        $course = $module->course;
        $module->delete();

        return redirect()->to($this->courseListUrl($course))->with('success', 'Modul berhasil dihapus.');
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

        return response()->json(['status' => 'success', 'message' => 'Urutan modul diperbarui.']);
    }
}
