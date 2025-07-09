<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    /**
     * Display a list of modules for a given course.
     */
    public function index(Course $course)
    {
        // Authorize that the current user owns the course
        if (Auth::id() !== $course->instructor_id) {
            abort(403);
        }

        // Eager load lessons for each module
        $modules = $course->modules()->with('lessons')->orderBy('order')->get();

        return view('instructor.modules.index', compact('course', 'modules'));
    }

    /**
     * Show the form for creating a new module.
     */
    public function create(Course $course)
    {
        if (Auth::id() !== $course->instructor_id) {
            abort(403);
        }
        return view('instructor.modules.create', compact('course'));
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(Request $request, Course $course)
    {
        if (Auth::id() !== $course->instructor_id) {
            abort(403);
        }

        $validated = $request->validate(['title' => 'required|string|max:255']);

        // Determine the order for the new module
        $lastOrder = $course->modules()->max('order') ?? 0;

        $course->modules()->create([
            'title' => $validated['title'],
            'order' => $lastOrder + 1,
        ]);

        return redirect()->route('instructor.courses.modules.index', $course)->with('success', 'Module created successfully.');
    }

    /**
     * Show the form for editing the specified module.
     */
    public function edit(Module $module)
    {
        if (Auth::id() !== $module->course->instructor_id) {
            abort(403);
        }
        return view('instructor.modules.edit', compact('module'));
    }

    /**
     * Update the specified module in storage.
     */
    public function update(Request $request, Module $module)
    {
        if (Auth::id() !== $module->course->instructor_id) {
            abort(403);
        }

        $validated = $request->validate(['title' => 'required|string|max:255']);

        $module->update($validated);

        return redirect()->route('instructor.courses.modules.index', $module->course)->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(Module $module)
    {
        if (Auth::id() !== $module->course->instructor_id) {
            abort(403);
        }

        $course = $module->course; // Get course before deleting for redirect
        $module->delete(); // This will also delete associated lessons due to cascade on delete

        return redirect()->route('instructor.courses.modules.index', $course)->with('success', 'Module deleted successfully.');
    }

    /**
     * Update the order of modules for a course.
     */
    public function reorder(Request $request, Course $course)
    {
        if (Auth::id() !== $course->instructor_id) {
            abort(403);
        }

        $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        foreach ($request->module_ids as $index => $moduleId) {
            Module::where('id', $moduleId)
                ->where('course_id', $course->id) // Extra security check
                ->update(['order' => $index + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Module order updated.']);
    }
}
