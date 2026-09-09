<?php

use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\ComplaintMonitoringController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MinistryManagementController;
use App\Http\Controllers\Admin\ProjectManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Ministry\ComplaintOperationsController;
use App\Http\Controllers\Ministry\DashboardController as MinistryDashboardController;
use App\Http\Controllers\Ministry\FieldAssignmentController;
use App\Http\Controllers\Ministry\TransferOperationsController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Transparency & Landing Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'welcome'])->name('home');

Route::prefix('public')->name('public.')->group(function () {
    Route::get('/complaints', [PublicController::class, 'complaintsIndex'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [PublicController::class, 'complaintShow'])->name('complaints.show');
    Route::get('/projects', [PublicController::class, 'projectsIndex'])->name('projects.index');
    Route::get('/projects/{project}', [PublicController::class, 'projectShow'])->name('projects.show');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes (Strict Middleware Authorization: 403 on Unauthorized)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'super_admin',
])->prefix('admin')->name('admin.')->group(function () {
    // Global Dashboard & Monitoring
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Global Complaints Oversight
    Route::get('/complaints', [ComplaintMonitoringController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintMonitoringController::class, 'show'])->name('complaints.show');

    // Ministries & Departments Management
    Route::get('/ministries', [MinistryManagementController::class, 'index'])->name('ministries.index');
    Route::POST('/ministries', [MinistryManagementController::class, 'storeMinistry'])->name('ministries.store');
    Route::POST('/departments', [MinistryManagementController::class, 'storeDepartment'])->name('departments.store');

    // Categories Management
    Route::get('/categories', [CategoryManagementController::class, 'index'])->name('categories.index');
    Route::POST('/categories', [CategoryManagementController::class, 'store'])->name('categories.store');

    // Users & Roles Management
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::POST('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::POST('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle_active');

    // Development Projects Management
    Route::get('/projects', [ProjectManagementController::class, 'index'])->name('projects.index');
    Route::POST('/projects', [ProjectManagementController::class, 'store'])->name('projects.store');
    Route::POST('/projects/{project}/update-progress', [ProjectManagementController::class, 'updateProgress'])->name('projects.update_progress');
});

/*
|--------------------------------------------------------------------------
| Ministry Admin Routes (Strict Middleware Authorization: 403 on Unauthorized)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'ministry_admin',
])->prefix('ministry')->name('ministry.')->group(function () {
    // Department Dashboard
    Route::get('/dashboard', [MinistryDashboardController::class, 'index'])->name('dashboard');

    // Operational Complaints Processing
    Route::get('/complaints', [ComplaintOperationsController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintOperationsController::class, 'show'])->name('complaints.show');
    Route::POST('/complaints/{complaint}/status', [ComplaintOperationsController::class, 'updateStatus'])->name('complaints.update_status');
    Route::POST('/complaints/{complaint}/assign', [ComplaintOperationsController::class, 'assignWorker'])->name('complaints.assign_worker');
    Route::POST('/complaints/{complaint}/transfer', [ComplaintOperationsController::class, 'transfer'])->name('complaints.transfer');

    // Field Tasks & Assignments
    Route::get('/assignments', [FieldAssignmentController::class, 'index'])->name('assignments.index');

    // Transfers
    Route::get('/transfers', [TransferOperationsController::class, 'index'])->name('transfers.index');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Default Dashboard (Citizen / Staff fallback)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('Ministry Admin')) {
            return redirect()->route('ministry.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');
});
