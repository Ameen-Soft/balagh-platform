<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Ministry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MinistryManagementController extends Controller
{
    /**
     * List all ministries and their departments.
     */
    public function index(): View
    {
        $ministries = Ministry::with(['departments.complaints', 'departments.users'])->latest()->get();

        return view('admin.ministries.index', compact('ministries'));
    }

    /**
     * Store a new ministry.
     */
    public function storeMinistry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ministries,name',
            'code' => 'required|string|max:50|unique:ministries,code',
            'contact_email' => 'required|email|max:255',
            'logo' => 'nullable|string|max:255',
        ]);

        $validated['logo'] = $validated['logo'] ?? 'logos/default.png';
        $validated['is_active'] = true;

        Ministry::create($validated);

        return back()->with('success', 'تمت إضافة الوزارة بنجاح.');
    }

    /**
     * Store a new department under a ministry.
     */
    public function storeDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
        ]);

        $validated['is_active'] = true;

        Department::create($validated);

        return back()->with('success', 'تمت إضافة الإدارة الخدمية بنجاح.');
    }
}
