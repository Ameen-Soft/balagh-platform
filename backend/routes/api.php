<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommunityComplaintController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\FieldAssignmentController;
use App\Http\Controllers\Api\V1\MinistryController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\SiteSettingApiController;
use App\Http\Controllers\Api\V1\TransferController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Balagh Platform
| All endpoints are versioned under /api/v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/google', [AuthController::class, 'googleLogin']);

        // Password Recovery
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
        Route::get('/reset-password-page', [AuthController::class, 'showResetPasswordPage'])->name('password.reset.page');

        // Email Verification (Signed URL & Public Resend)
        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        Route::post('/email/resend', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1');
    });

    // Reference Data: Ministries, Categories & Public Settings
    Route::get('/settings', [SiteSettingApiController::class, 'index']);
    Route::get('/ministries', [MinistryController::class, 'index']);
    Route::get('/ministries/{ministry}', [MinistryController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // Public Project Discovery
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    // Community Complaints Public Feed & Map Discovery
    Route::get('/complaints/public', [CommunityComplaintController::class, 'index']);
    Route::get('/complaints/public/{id}', [CommunityComplaintController::class, 'show']);
    Route::get('/complaints/map', [CommunityComplaintController::class, 'map']);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Requires Sanctum Token & Active Account)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'active_user'])->group(function () {

        // Authenticated User & Session
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/me', [AuthController::class, 'updateProfile']);
            Route::post('/logout', [AuthController::class, 'logout']);

            // Email Verification for Authenticated User
            Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1');
            Route::get('/email/status', [AuthController::class, 'getVerificationStatus']);
        });

        // User Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        });

        // Complaints Management
        Route::prefix('complaints')->group(function () {
            Route::get('/', [ComplaintController::class, 'index']);
            Route::post('/', [ComplaintController::class, 'store']);
            Route::get('/{complaint}', [ComplaintController::class, 'show']);
            Route::patch('/{complaint}/status', [ComplaintController::class, 'updateStatus']);

            // Community Interaction (Like / Unlike)
            Route::post('/{id}/like', [CommunityComplaintController::class, 'like']);
            Route::delete('/{id}/like', [CommunityComplaintController::class, 'unlike']);

            // Complaint Inter-Department Transfers
            Route::post('/{complaint}/transfer', [TransferController::class, 'transfer']);

            // Field Worker Assignment
            Route::post('/{complaint}/assign', [FieldAssignmentController::class, 'assign']);
        });

        // Field Work & Assignments
        Route::prefix('field-assignments')->group(function () {
            Route::get('/', [FieldAssignmentController::class, 'index']);
            Route::get('/{assignment}', [FieldAssignmentController::class, 'show']);
            Route::patch('/{assignment}/accept', [FieldAssignmentController::class, 'accept']);
            Route::post('/{assignment}/start', [FieldAssignmentController::class, 'start']);
            Route::post('/{assignment}/verify-location', [FieldAssignmentController::class, 'verifyLocation']);
            Route::post('/{assignment}/complete', [FieldAssignmentController::class, 'complete']);
        });

        // Developmental Projects Management & Community Contributions
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::post('/projects/{project}/contribute', [ProjectController::class, 'contribute']);
    });
});
