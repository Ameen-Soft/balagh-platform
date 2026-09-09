<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * List users with role filter and search.
     */
    public function index(Request $request): View
    {
        $roleName = $request->get('role');
        $search = $request->get('search');

        $query = User::with(['roles', 'department.ministry']);

        if ($roleName) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $roleName));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::all();
        $departments = Department::with('ministry')->get();

        return view('admin.users.index', compact('users', 'roles', 'departments', 'roleName', 'search'));
    }

    /**
     * Store a new administrative or field user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'national_id' => 'required|string|max:20|unique:users,national_id',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'national_id' => $validated['national_id'],
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->roles()->sync([$validated['role_id']]);

        return back()->with('success', 'تم إنشاء حساب المستخدم وتعيين الصلاحية بنجاح.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        // Don't deactivate self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك تعطيل حسابك الشخصي الحالي.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'تم تحديث حالة تفعيل الحساب بنجاح.');
    }
}
