<?php

use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\ComplaintMonitoringController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MinistryManagementController;
use App\Http\Controllers\Admin\ProjectManagementController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Ministry\ComplaintOperationsController;
use App\Http\Controllers\Ministry\DashboardController as MinistryDashboardController;
use App\Http\Controllers\Ministry\FieldAssignmentController;
use App\Http\Controllers\Ministry\MinistryCategoryController;
use App\Http\Controllers\Ministry\MinistryDepartmentController;
use App\Http\Controllers\Ministry\MinistryProjectController;
use App\Http\Controllers\Ministry\TransferOperationsController;
use App\Http\Controllers\Ministry\WorkerManagementController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\UserSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Transparency & Landing Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'welcome'])->name('home');

Route::name('public.')->group(function () {
    Route::get('/complaints', [PublicController::class, 'complaintsIndex'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [PublicController::class, 'complaintShow'])->name('complaints.show');
    Route::get('/projects', [PublicController::class, 'projectsIndex'])->name('projects.index');
    Route::get('/projects/{project}', [PublicController::class, 'projectShow'])->name('projects.show');
});

Route::prefix('public')->group(function () {
    Route::get('/complaints', [PublicController::class, 'complaintsIndex']);
    Route::get('/complaints/{complaint}', [PublicController::class, 'complaintShow']);
    Route::get('/projects', [PublicController::class, 'projectsIndex']);
    Route::get('/projects/{project}', [PublicController::class, 'projectShow']);
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
    Route::post('/ministries', [MinistryManagementController::class, 'storeMinistry'])->name('ministries.store');
    Route::post('/ministries/{ministry}/assign-admin', [MinistryManagementController::class, 'assignAdmin'])->name('ministries.assign_admin');
    Route::post('/departments', [MinistryManagementController::class, 'storeDepartment'])->name('departments.store');

    // Categories Management
    Route::get('/categories', [CategoryManagementController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryManagementController::class, 'store'])->name('categories.store');

    // Users & Roles Management
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle_active');

    // Development Projects Management
    Route::get('/projects', [ProjectManagementController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectManagementController::class, 'store'])->name('projects.store');
    Route::match(['put', 'post'], '/projects/{project}', [ProjectManagementController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectManagementController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/update-progress', [ProjectManagementController::class, 'updateProgress'])->name('projects.update_progress');

    // Project Phases Management
    Route::post('/projects/{project}/phases', [ProjectManagementController::class, 'storePhase'])->name('projects.phases.store');
    Route::match(['put', 'post'], '/projects/{project}/phases/{phase}', [ProjectManagementController::class, 'updatePhase'])->name('projects.phases.update');
    Route::delete('/projects/{project}/phases/{phase}', [ProjectManagementController::class, 'destroyPhase'])->name('projects.phases.destroy');

    // Platform Identity & Head Settings
    Route::get('/settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SiteSettingController::class, 'update'])->name('settings.update');
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
    Route::post('/complaints/{complaint}/status', [ComplaintOperationsController::class, 'updateStatus'])->name('complaints.update_status');
    Route::post('/complaints/{complaint}/assign', [ComplaintOperationsController::class, 'assignWorker'])->name('complaints.assign_worker');
    Route::post('/complaints/{complaint}/transfer', [ComplaintOperationsController::class, 'transfer'])->name('complaints.transfer');

    // Service Departments Management
    Route::get('/departments', [MinistryDepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [MinistryDepartmentController::class, 'store'])->name('departments.store');

    // Complaint Categories Management
    Route::get('/categories', [MinistryCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [MinistryCategoryController::class, 'store'])->name('categories.store');

    // Field Tasks & Assignments
    Route::get('/assignments', [FieldAssignmentController::class, 'index'])->name('assignments.index');

    // Field Workers Management
    Route::get('/workers', [WorkerManagementController::class, 'index'])->name('workers.index');
    Route::post('/workers/{user}/promote', [WorkerManagementController::class, 'promote'])->name('workers.promote');
    Route::post('/workers/{user}/demote', [WorkerManagementController::class, 'demote'])->name('workers.demote');

    // Transfers
    Route::get('/transfers', [TransferOperationsController::class, 'index'])->name('transfers.index');

    // Developmental Projects Management
    Route::get('/projects', [MinistryProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [MinistryProjectController::class, 'store'])->name('projects.store');
    Route::match(['put', 'post'], '/projects/{project}', [MinistryProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [MinistryProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/update-progress', [MinistryProjectController::class, 'updateProgress'])->name('projects.update_progress');

    // Project Phases Management
    Route::post('/projects/{project}/phases', [MinistryProjectController::class, 'storePhase'])->name('projects.phases.store');
    Route::match(['put', 'post'], '/projects/{project}/phases/{phase}', [MinistryProjectController::class, 'updatePhase'])->name('projects.phases.update');
    Route::delete('/projects/{project}/phases/{phase}', [MinistryProjectController::class, 'destroyPhase'])->name('projects.phases.destroy');
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

    // High-Performance Unified User Search Endpoint
    Route::get('/internal/search/users', [UserSearchController::class, 'search'])
        ->middleware('throttle:60,1')
        ->name('internal.search.users');
});
