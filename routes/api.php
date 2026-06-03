<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Expense routes
    Route::apiResource('expenses', ExpenseController::class);

    // User management routes
    Route::apiResource('users', UserController::class);

    // Audit logs routes
    Route::apiResource('audit-logs', AuditLogController::class, ['only' => ['index', 'show']]);
});
