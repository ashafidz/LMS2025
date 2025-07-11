<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a list of courses for the authenticated instructor.
     */
    public function index()
    {
        $courses = Auth::user()->courses()->with('category')->latest()->paginate(10);
        return view('instructor.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        $categories = CourseCategory::orderBy('name')->get();
        return view('instructor.courses.create', compact('categories'));
    }

    /**
     * Store a newly created course in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:course_categories,id',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability_type' => ['required', Rule::in(['lifetime', 'period'])],
            // 'start_date' and 'end_date' are required only if type is 'period'
            'start_date' => 'required_if:availability_type,period|nullable|date',
            'end_date' => 'required_if:availability_type,period|nullable|date|after_or_equal:start_date',
        ]);

        $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');

        Auth::user()->courses()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'thumbnail_url' => $thumbnailPath,
            'status' => 'draft',
            // Save the new fields
            'availability_type' => $validated['availability_type'],
            'start_date' => $validated['availability_type'] === 'period' ? $validated['start_date'] : null,
            'end_date' => $validated['availability_type'] === 'period' ? $validated['end_date'] : null,
        ]);

        return redirect()->route('instructor.courses.index')->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        // Authorization Check
        if (Auth::id() !== $course->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('instructor.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course)
    {
        // Authorization Check
        if (Auth::id() !== $course->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

        $categories = CourseCategory::orderBy('name')->get();
        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        // Authorization Check
        if (Auth::id() !== $course->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:course_categories,id',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['draft', 'pending_review', 'published', 'rejected', 'private'])],
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability_type' => ['required', Rule::in(['lifetime', 'period'])],
            'start_date' => 'required_if:availability_type,period|nullable|date',
            'end_date' => 'required_if:availability_type,period|nullable|date|after_or_equal:start_date',
        ]);

        $course->title = $validated['title'];
        $course->slug = Str::slug($validated['title']);
        // ... (rest of the fields are the same)
        $course->status = $validated['status'];

        // Update availability fields
        $course->availability_type = $validated['availability_type'];
        $course->start_date = $validated['availability_type'] === 'period' ? $validated['start_date'] : null;
        $course->end_date = $validated['availability_type'] === 'period' ? $validated['end_date'] : null;

        if ($request->hasFile('thumbnail')) {
            // ... (thumbnail logic is unchanged)
        }

        $course->save();

        return redirect()->route('instructor.courses.index')->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course)
    {
        // Authorization Check
        if (Auth::id() !== $course->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the thumbnail from storage
        if ($course->thumbnail_url) {
            Storage::disk('public')->delete($course->thumbnail_url);
        }

        // Soft delete the course
        $course->delete();

        return redirect()->route('instructor.courses.index')->with('success', 'Course deleted successfully.');
    }
}
