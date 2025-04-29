<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchTargetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CircularController;
use App\Http\Controllers\ComplaintAttachmentController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DailyPositionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\DocController;

use App\Http\Controllers\HrdController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrintedStationeryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StationeryTransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserModuleController;
use Illuminate\Support\Facades\Route;






Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/daily-positions', [DashboardController::class, 'daily_position'])->name('dashboard.daily-positions');


    Route::get('product', [ProductController::class, 'product'])->name('product.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');


    Route::resource('/product/daily-positions', DailyPositionController::class);
    Route::get('daily-positions/{id}', [DailyPositionController::class, 'show'])->name('daily-positions.view');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/branch', [SettingController::class, 'branchSetting'])->name('settings.branchsetting');
    Route::resource('/settings/branch-targets', BranchTargetController::class);
    Route::resource('/settings/branch/branches', BranchController::class);
    Route::resource('/settings/branch/regions', RegionController::class);
    Route::resource('/settings/branch/districts', DistrictController::class);
    Route::resource('/settings/roles', RoleController::class);
    Route::resource('/settings/permissions', PermissionController::class)->middleware('auth');
    Route::resource('/settings/users', UserController::class);

    // Updated route to point to the ReportController's method
    Route::get('reports/daily-position-report', [ReportController::class, 'dailyPositionReport'])->name('reports.daily-position-report');
    Route::get('reports/deposit-advances-reports-branch', [ReportController::class, 'depositadvancesregionPositionReport'])->name('reports.deposit-advances-reports-branch');
    Route::get('reports/deposit-advances-reports-region', [ReportController::class, 'depositadvancesPositionReport'])->name('reports.deposit-advances-reports-region');
    Route::get('reports/accounts-branchwise-reports', [ReportController::class, 'accountsbranchwisePositionReport'])->name('reports.accounts-branchwise-reports');
    Route::get('settings/user-module', [UserModuleController::class, 'index'])->name('user.module');
    Route::get('settings/user-module/users', [UserController::class, 'index'])->name('users.index');
    Route::get('settings/user-module/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('settings/user-module/roles', [RoleController::class, 'index'])->name('roles.index');


    Route::get('reports/accounts-regionwise-reports', [ReportController::class, 'accountsregionwisePositionReport'])->name('reports.accounts-regionwise-reports');
    Route::resource('/products/circulars', CircularController::class)->except(['destroy']);
    Route::resource('/products/complaints', ComplaintController::class);
    Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.update-status');
    Route::get('/complaints/attachments/{attachment}/download', [ComplaintAttachmentController::class, 'download'])->name('complaints.attachments.download');
    Route::resource('settings/user-module/managers', ManagerController::class);

    Route::resource('hrd', HrdController::class);
    Route::resource('manual', ManualController::class);
    Route::resource('/settings/categories', CategoryController::class);

    Route::resource('/products/docs', DocController::class);



    Route::resource('printed-stationeries', PrintedStationeryController::class);
    Route::resource('stationery-transactions', StationeryTransactionController::class);


});
