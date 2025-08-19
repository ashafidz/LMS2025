<?php

namespace App\Http\Controllers\Shared;

use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::with('course')->latest()->simplePaginate(15);
        return view('shared-admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $courses = Course::where('status', 'published')->orderBy('title')->get();
        return view('shared-admin.coupons.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     */
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'code' => 'required|string|unique:coupons,code|max:50',
    //         'description' => 'nullable|string',
    //         'type' => ['required', Rule::in(['fixed', 'percent'])],
    //         'value' => 'required|numeric|min:0',
    //         'course_id' => 'nullable|exists:courses,id',
    //         'max_uses' => 'nullable|integer|min:1',
    //         'starts_at' => 'nullable|date',
    //         'expires_at' => 'nullable|date|after_or_equal:starts_at',
    //         'is_active' => 'boolean',
    //     ]);

    //     // Pastikan nilai persen tidak lebih dari 100
    //     if ($request->type === 'percent' && $request->value > 100) {
    //         return back()->withInput()->withErrors(['value' => 'Nilai persentase tidak boleh lebih dari 100.']);
    //     }

    //     Coupon::create($validated);
    //     $roleName = Auth::user()->getRoleNames()->first();


    //     return redirect()->route($roleName . '.coupons.index')->with('success', 'Kupon berhasil dibuat.');
    // }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:50',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => 'required|numeric|min:0',
            'is_public' => 'boolean', // Tambahkan validasi
            'course_id' => 'nullable|exists:courses,id',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1', // Tambahkan validasi
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        if ($request->type === 'percent' && $request->value > 100) {
            return back()->withInput()->withErrors(['value' => 'Nilai persentase tidak boleh lebih dari 100.']);
        }

        Coupon::create($validated);
        $roleName = Auth::user()->getRoleNames()->first();
        return redirect()->route($roleName . '.coupons.index')->with('success', 'Kupon berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit(Coupon $coupon)
    {
        $courses = Course::where('status', 'published')->orderBy('title')->get();
        return view('shared-admin.coupons.edit', compact('coupon', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     *
     */
    // public function update(Request $request, Coupon $coupon)
    // {
    //     $validated = $request->validate([
    //         'code' => ['required', 'string', 'max:50', Rule::unique('coupons')->ignore($coupon->id)],
    //         'description' => 'nullable|string',
    //         'type' => ['required', Rule::in(['fixed', 'percent'])],
    //         'value' => 'required|numeric|min:0',
    //         'course_id' => 'nullable|exists:courses,id',
    //         'max_uses' => 'nullable|integer|min:1',
    //         'starts_at' => 'nullable|date',
    //         'expires_at' => 'nullable|date|after_or_equal:starts_at',
    //         'is_active' => 'boolean',
    //     ]);

    //     if ($request->type === 'percent' && $request->value > 100) {
    //         return back()->withInput()->withErrors(['value' => 'Nilai persentase tidak boleh lebih dari 100.']);
    //     }

    //     $coupon->update($validated);
    //     $roleName = Auth::user()->getRoleNames()->first();


    //     return redirect()->route($roleName . '.coupons.index')->with('success', 'Kupon berhasil diperbarui.');
    // }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons')->ignore($coupon->id)],
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => 'required|numeric|min:0',
            'is_public' => 'boolean', // Tambahkan validasi
            'course_id' => 'nullable|exists:courses,id',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1', // Tambahkan validasi
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        if ($request->type === 'percent' && $request->value > 100) {
            return back()->withInput()->withErrors(['value' => 'Nilai persentase tidak boleh lebih dari 100.']);
        }

        $coupon->update($validated);
        $roleName = Auth::user()->getRoleNames()->first();
        return redirect()->route($roleName . '.coupons.index')->with('success', 'Kupon berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Kupon berhasil dihapus.');
    }
}
