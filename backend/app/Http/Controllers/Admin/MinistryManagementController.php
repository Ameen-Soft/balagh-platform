<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MinistryManagementController extends Controller
{
    /**
     * List all ministries and their departments, plus eligible users for admin assignment.
     */
    public function index(): View
    {
        $ministries = Ministry::with([
            'departments.complaints',
            'departments.users.roles',
        ])->latest()->get();

        $eligibleUsers = User::where('is_active', true)
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'Super Admin'))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'department_id']);

        return view('admin.ministries.index', compact('ministries', 'eligibleUsers'));
    }

    /**
     * Store a new ministry without requiring code, with optional responsible admin assignment.
     */
    public function storeMinistry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ministries,name',
            'contact_email' => 'required|email|max:255',
            'logo' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:ministries,code',
            'admin_user_id' => 'nullable|exists:users,id',
        ], [
            'name.required' => 'اسم الوزارة مطلوب.',
            'name.unique' => 'اسم الوزارة مسجل مسبقاً في النظام.',
            'contact_email.required' => 'البريد الإلكتروني الرسمي للوزارة مطلوب.',
            'contact_email.email' => 'صيغة البريد الإلكتروني غير صالحة.',
            'admin_user_id.exists' => 'المستخدم المختار غير موجود في النظام.',
        ]);

        $adminUser = null;
        if (! empty($validated['admin_user_id'])) {
            $adminUser = User::findOrFail($validated['admin_user_id']);

            if (! $adminUser->is_active) {
                throw ValidationException::withMessages([
                    'admin_user_id' => 'لا يمكن اختيار مستخدم حسابه غير نشط حالياً.',
                ]);
            }

            if ($adminUser->hasRole('Super Admin')) {
                throw ValidationException::withMessages([
                    'admin_user_id' => 'لا يمكن تعيين المدير العام للنظام مسؤولاً عن وزارة.',
                ]);
            }
        }

        $code = $validated['code'] ?? ('MIN-' . strtoupper(Str::random(6)));

        $ministry = Ministry::create([
            'name' => $validated['name'],
            'code' => $code,
            'contact_email' => $validated['contact_email'],
            'logo' => $validated['logo'] ?? 'logos/default.png',
            'is_active' => true,
        ]);

        // If an admin user was selected, link them to the ministry
        if ($adminUser) {
            $defaultDept = Department::create([
                'ministry_id' => $ministry->id,
                'name' => 'ديوان عام الوزارة',
                'description' => 'المقر العام والإدارة التنفيذية للوزارة',
                'is_active' => true,
            ]);

            $adminUser->update(['department_id' => $defaultDept->id]);

            $ministryAdminRole = Role::firstOrCreate(
                ['name' => 'Ministry Admin'],
                ['guard_name' => 'web']
            );
            $adminUser->roles()->syncWithoutDetaching([$ministryAdminRole->id]);
        }

        return back()->with('success', 'تمت إضافة الوزارة بنجاح.');
    }

    /**
     * Assign or update the responsible administrator for a ministry.
     */
    public function assignAdmin(Request $request, Ministry $ministry): RedirectResponse
    {
        $validated = $request->validate([
            'admin_user_id' => 'required|exists:users,id',
        ], [
            'admin_user_id.required' => 'يرجى اختيار المسؤول عن الوزارة.',
            'admin_user_id.exists' => 'المستخدم المختار غير موجود في النظام.',
        ]);

        $adminUser = User::findOrFail($validated['admin_user_id']);

        if (! $adminUser->is_active) {
            throw ValidationException::withMessages([
                'admin_user_id' => 'لا يمكن تعيين مستخدم حسابه معطل كمسؤول عن الوزارة.',
            ]);
        }

        if ($adminUser->hasRole('Super Admin')) {
            throw ValidationException::withMessages([
                'admin_user_id' => 'لا يمكن تعيين المدير العام للنظام كمسؤول عن وزارة.',
            ]);
        }

        $department = $ministry->departments()->first();
        if (! $department) {
            $department = Department::create([
                'ministry_id' => $ministry->id,
                'name' => 'ديوان عام الوزارة',
                'description' => 'المقر العام والإدارة التنفيذية للوزارة',
                'is_active' => true,
            ]);
        }

        $adminUser->update(['department_id' => $department->id]);

        $ministryAdminRole = Role::firstOrCreate(
            ['name' => 'Ministry Admin'],
            ['guard_name' => 'web']
        );
        $adminUser->roles()->syncWithoutDetaching([$ministryAdminRole->id]);

        return back()->with('success', "تم تعيين {$adminUser->name} مسؤولاً عن {$ministry->name} بنجاح.");
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
