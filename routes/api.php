<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\FieldAssignmentController;
use App\Http\Controllers\Api\V1\ProjectController;
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
    });

    // Public Project Discovery
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Requires Sanctum Token)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Authenticated User & Session
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        // Complaints Management
        Route::prefix('complaints')->group(function () {
            Route::get('/', [ComplaintController::class, 'index']);
            Route::post('/', [ComplaintController::class, 'store']);
            Route::get('/{complaint}', [ComplaintController::class, 'show']);
            Route::patch('/{complaint}/status', [ComplaintController::class, 'updateStatus']);

            // Complaint Inter-Department Transfers
            Route::post('/{complaint}/transfer', [TransferController::class, 'transfer']);

            // Field Worker Assignment
            Route::post('/{complaint}/assign', [FieldAssignmentController::class, 'assign']);
        });

        // Field Work & Assignments
        Route::prefix('field-assignments')->group(function () {
            Route::get('/', [FieldAssignmentController::class, 'index']);
            Route::post('/{assignment}/start', [FieldAssignmentController::class, 'start']);
            Route::post('/{assignment}/complete', [FieldAssignmentController::class, 'complete']);
        });

        // Developmental Project Community Contributions
        Route::post('/projects/{project}/contribute', [ProjectController::class, 'contribute']);
    });
});
