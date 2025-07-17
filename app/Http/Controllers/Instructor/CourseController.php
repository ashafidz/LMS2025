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
    public function index()
    {
        $courses = Auth::user()->courses()->with('category')->latest()->paginate(10);
        return view('instructor.courses.index', compact('courses'));
    }

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
            // DIUBAH: Dari 'required' menjadi 'nullable' agar deskripsi boleh kosong
            'description' => 'nullable|string',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability_type' => ['required', Rule::in(['lifetime', 'period'])],
            'start_date' => 'required_if:availability_type,period|nullable|date',
            'end_date' => 'required_if:availability_type,period|nullable|date|after_or_equal:start_date',
        ]);

        $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');

        Auth::user()->courses()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'price' => 0,
            'thumbnail_url' => $thumbnailPath,
            'status' => 'draft',
            'availability_type' => $validated['availability_type'],
            'start_date' => $validated['availability_type'] === 'period' ? $validated['start_date'] : null,
            'end_date' => $validated['availability_type'] === 'period' ? $validated['end_date'] : null,
        ]);

        return redirect()->route('instructor.courses.index')->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        return view('instructor.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $categories = CourseCategory::orderBy('name')->get();
        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:course_categories,id',
            'description' => 'nullable|string',
            // DIHAPUS: Aturan validasi untuk 'status' dihapus dari sini
            // 'status' => ['required', Rule::in(['draft', ...])], 
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability_type' => ['required', Rule::in(['lifetime', 'period'])],
            'start_date' => 'required_if:availability_type,period|nullable|date',
            'end_date' => 'required_if:availability_type,period|nullable|date|after_or_equal:start_date',
        ]);

        $course->title = $validated['title'];
        $course->slug = Str::slug($validated['title']);
        $course->category_id = $validated['category_id'];
        $course->description = $validated['description'];
        // Kita tidak lagi memperbarui status dari sini
        // $course->status = $validated['status']; 
        $course->availability_type = $validated['availability_type'];
        $course->start_date = $validated['availability_type'] === 'period' ? $validated['start_date'] : null;
        $course->end_date = $validated['availability_type'] === 'period' ? $validated['end_date'] : null;

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_url) {
                Storage::disk('public')->delete($course->thumbnail_url);
            }
            $course->thumbnail_url = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->save();

        return redirect()->route('instructor.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        if ($course->thumbnail_url) {
            Storage::disk('public')->delete($course->thumbnail_url);
        }
        $course->delete();
        return redirect()->route('instructor.courses.index')->with('success', 'Course deleted successfully.');
    }

    /**
     * Mengajukan kursus untuk direview oleh admin.
     */
    public function submitForReview(Course $course)
    {
        // Hanya bisa diajukan jika statusnya draft atau ditolak
        if (in_array($course->status, ['draft', 'rejected'])) {
            $course->status = 'pending_review';
            $course->save();
            return back()->with('success', 'Kursus berhasil diajukan untuk direview.');
        }
        return back()->with('error', 'Aksi tidak valid.');
    }

    /**
     * Mengubah status kursus menjadi privat.
     */
    public function makePrivate(Course $course)
    {
        // Hanya bisa dijadikan privat jika statusnya draft atau sudah dipublikasi
        if (in_array($course->status, ['draft', 'published'])) {
            $course->status = 'private';
            $course->save();
            return back()->with('success', 'Status kursus berhasil diubah menjadi privat.');
        }
        return back()->with('error', 'Aksi tidak valid.');
    }
}
