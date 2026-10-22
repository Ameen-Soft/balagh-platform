<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MinistryDepartmentController extends Controller
{
    /**
     * Display a listing of departments strictly scoped to the logged-in user's ministry.
     */
    public function index(): View
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة في النظام.');
        }

        $departments = Department::where('ministry_id', $ministry->id)
            ->withCount(['complaints', 'users', 'categories'])
            ->latest()
            ->get();

        return view('ministry.departments.index', compact('departments', 'ministry'));
    }

    /**
     * Store a new service department automatically linked to the user's ministry.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry) {
            abort(403, 'غير مصرح: الحساب غير مرتبط بوزارة معتمدة.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
        ], [
            'name.required' => 'اسم الإدارة الخدمية مطلوب.',
            'description.required' => 'الوصف والاختصاص مطلوب.',
        ]);

        Department::create([
            'ministry_id' => $ministry->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'is_active' => true,
        ]);

        return redirect()->route('ministry.departments.index')->with('success', 'تم تسجيل الإدارة الخدمية الفرعية بنجاح.');
    }
}
