<?php

use App\Http\Controllers\DailyPositionController;
use App\Http\Controllers\BranchTargetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
Route::resource('permissions', PermissionController::class)->middleware('auth');



Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::get('product', [ProductController::class, 'product'])->name('product.index');

    Route::resource('daily-positions', DailyPositionController::class);
    Route::get('daily-positions/{id}', [DailyPositionController::class, 'view'])->name('daily-positions.view');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    // Updated route to point to the ReportController's method
    Route::get('reports/daily-position-report', [ReportController::class, 'dailyPositionReport'])
        ->name('reports.daily-position-report');

    Route::get('/settings/branchseting', [SettingController::class, 'branchSetting'])->name('settings.branchsetting');
    Route::resource('branch-targets', BranchTargetController::class);
    Route::resource('branches', BranchController::class);
    Route::resource('regions', RegionController::class);
    Route::resource('districts', DistrictController::class);
    Route::resource('roles', RoleController::class);


});
