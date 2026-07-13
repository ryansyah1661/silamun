<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SpeciesController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CarbonController;
use App\Http\Controllers\Api\ValidationController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| SI-LAMUN API Routes
|--------------------------------------------------------------------------
| All routes are prefixed with /api/v1
*/

Route::prefix('v1')->group(function () {

    // ============================================================
    // PUBLIC ROUTES (No Authentication Required)
    // ============================================================

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Public Dashboard Statistics
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/trends', [DashboardController::class, 'trends']);
        Route::get('/comparison', [DashboardController::class, 'comparison']);
        Route::get('/recent', [DashboardController::class, 'recentSurveys']);
    });

    // Public Map (GeoJSON endpoints)
    Route::prefix('map')->group(function () {
        Route::get('/surveys', [MapController::class, 'surveyPoints']);
        Route::get('/regions', [MapController::class, 'regionBoundaries']);
        Route::get('/bbox', [MapController::class, 'boundingBox']);
        Route::get('/choropleth', [MapController::class, 'choropleth']);
    });

    // Public Species Catalog
    Route::get('/species', [SpeciesController::class, 'index']);
    Route::get('/species/{species:slug}', [SpeciesController::class, 'show']);

    // Public Regions
    Route::get('/regions', [RegionController::class, 'index']);
    Route::get('/regions/provinces', [RegionController::class, 'provinces']);
    Route::get('/regions/kabupaten', [RegionController::class, 'kabupaten']);
    Route::get('/regions/{region}', [RegionController::class, 'show']);

    // Public Blue Carbon
    Route::prefix('carbon')->group(function () {
        Route::get('/national', [CarbonController::class, 'national']);
        Route::get('/region', [CarbonController::class, 'byRegion']);
        Route::post('/calculate', [CarbonController::class, 'calculate']);
        Route::get('/trend', [CarbonController::class, 'trend']);
    });

    // ============================================================
    // AUTHENTICATED ROUTES (Require Sanctum Token)
    // ============================================================

    Route::middleware('auth:sanctum')->group(function () {

        // Auth Management
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

        // Survey CRUD (All authenticated users)
        Route::apiResource('surveys', SurveyController::class);
        Route::post('/surveys/{survey}/submit', [SurveyController::class, 'submit'])
            ->name('surveys.submit');

        // ============================================================
        // VERIFIKATOR + ADMIN ROUTES
        // ============================================================

        Route::middleware('role:verifikator,super_admin')->group(function () {
            Route::get('/validation/queue', [ValidationController::class, 'queue']);
            Route::post('/validation/{survey}/review', [ValidationController::class, 'review']);
            Route::get('/validation/history', [ValidationController::class, 'history']);
        });

        // ============================================================
        // SUPER ADMIN ONLY ROUTES
        // ============================================================

        Route::middleware('role:super_admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'overview']);
            Route::get('/users', [AdminDashboardController::class, 'userStats']);

            // Admin Species CRUD (create, update, delete)
            Route::post('/species', [SpeciesController::class, 'store']);
            Route::put('/species/{species}', [SpeciesController::class, 'update']);
            Route::delete('/species/{species}', [SpeciesController::class, 'destroy']);
        });
    });
});
