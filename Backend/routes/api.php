<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\Api\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Api\Admin\MediaController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\Admin\SettingsController;
use App\Http\Controllers\Api\Admin\TourPackageController as AdminTourPackageController;
use App\Http\Controllers\Api\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\Public\EnquiryController as PublicEnquiryController;
use App\Http\Controllers\Api\Public\LocationController as PublicLocationController;
use App\Http\Controllers\Api\Public\ProductController as PublicProductController;
use App\Http\Controllers\Api\Public\PropertyController as PublicPropertyController;
use App\Http\Controllers\Api\Public\ServiceController as PublicServiceController;
use App\Http\Controllers\Api\Public\TourPackageController as PublicTourPackageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Universal Business Admin Platform
|--------------------------------------------------------------------------
| Two completely separate route groups, as required by the architecture:
|   /api/admin/*   authenticated (Sanctum) + role-checked, used by Lizy Admin
|   /api/public/*  unauthenticated, read-mostly, consumed by React sites
*/

// ---------------------------------------------------------------------
// ADMIN API
// ---------------------------------------------------------------------
Route::prefix('admin')->group(function () {

    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'updateProfile']);
        Route::put('me/password', [AuthController::class, 'updatePassword']);
        Route::get('me/sessions', [AuthController::class, 'sessions']);
        Route::delete('me/sessions/others', [AuthController::class, 'logoutOtherSessions']);
        Route::delete('me/sessions/{id}', [AuthController::class, 'logoutSession']);

        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::get('settings', [SettingsController::class, 'index']);
        Route::put('settings/general', [SettingsController::class, 'updateGeneral']);
        Route::put('settings/modules', [SettingsController::class, 'updateModules']);
        Route::put('settings/website', [SettingsController::class, 'updateWebsite']);
        Route::put('settings/notifications', [SettingsController::class, 'updateNotifications']);

        Route::apiResource('categories', AdminCategoryController::class);
        Route::apiResource('locations', AdminLocationController::class);

        Route::get('media', [MediaController::class, 'index']);
        Route::post('media', [MediaController::class, 'store']);
        Route::put('media/{id}', [MediaController::class, 'update']);
        Route::delete('media/{id}', [MediaController::class, 'destroy']);

        Route::get('enquiries', [AdminEnquiryController::class, 'index']);
        Route::post('enquiries', [AdminEnquiryController::class, 'store']);
        Route::post('enquiries/mark-read', [AdminEnquiryController::class, 'markRead']);
        Route::get('enquiries/{id}', [AdminEnquiryController::class, 'show']);
        Route::put('enquiries/{id}', [AdminEnquiryController::class, 'update']);

        // Only Administrators/Managers/Content Editors may write; everyone above may read.
        Route::middleware('role:administrator,manager,content-editor')->group(function () {
            foreach ([
                'products' => AdminProductController::class,
                'properties' => AdminPropertyController::class,
                'packages' => AdminTourPackageController::class,
                'services' => AdminServiceController::class,
            ] as $prefix => $controller) {
                Route::post("{$prefix}", [$controller, 'store']);
                Route::put("{$prefix}/{id}", [$controller, 'update']);
                Route::delete("{$prefix}/{id}", [$controller, 'destroy']);
                Route::post("{$prefix}/{id}/restore", [$controller, 'restore']);
                Route::delete("{$prefix}/{id}/force", [$controller, 'forceDelete']);
                Route::post("{$prefix}/bulk-status", [$controller, 'bulkStatus']);
                Route::post("{$prefix}/bulk-category", [$controller, 'bulkCategory']);
                Route::post("{$prefix}/bulk-delete", [$controller, 'bulkDelete']);
                Route::post("{$prefix}/import", [$controller, 'importCsv']);
            }
        });

        // Every authenticated admin role (including Viewer) can read.
        foreach ([
            'products' => AdminProductController::class,
            'properties' => AdminPropertyController::class,
            'packages' => AdminTourPackageController::class,
            'services' => AdminServiceController::class,
        ] as $prefix => $controller) {
            Route::get("{$prefix}", [$controller, 'index']);
            Route::get("{$prefix}/{id}", [$controller, 'show']);
        }
    });
});

// ---------------------------------------------------------------------
// PUBLIC API (consumed by the React websites - read-only, published only)
// ---------------------------------------------------------------------
Route::prefix('public')->middleware('throttle:120,1')->group(function () {

    Route::get('categories', [PublicCategoryController::class, 'index']);
    Route::get('locations', [PublicLocationController::class, 'index']);
    Route::post('enquiries', [PublicEnquiryController::class, 'store']);

    Route::get('products', [PublicProductController::class, 'index']);
    Route::get('products/{slug}', [PublicProductController::class, 'show']);

    Route::get('properties', [PublicPropertyController::class, 'index']);
    Route::get('properties/{slug}', [PublicPropertyController::class, 'show']);

    Route::get('packages', [PublicTourPackageController::class, 'index']);
    Route::get('packages/{slug}', [PublicTourPackageController::class, 'show']);

    Route::get('services', [PublicServiceController::class, 'index']);
    Route::get('services/{slug}', [PublicServiceController::class, 'show']);
});
