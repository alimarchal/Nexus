<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocController;
use App\Http\Controllers\HrdController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;

use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CircularController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserModuleController;
use App\Http\Controllers\BranchTargetController;
use App\Http\Controllers\DailyPositionController;
use App\Http\Controllers\DispatchRegisterController;
use App\Http\Controllers\PrintedStationeryController;
use App\Http\Controllers\ComplaintAttachmentController;
use App\Http\Controllers\ComplaintStatusTypeController;
use App\Http\Controllers\StationeryTransactionController;






Route::get('/', function () {
    return to_route('login'); // Redirect to the login route
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
    // Route::get('reports/stationary-branchwise-reports', [ReportController::class, 'stationarybranchwisereport'])->name('stationary-branchwise-reports');
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

    Route::resource('/settings/categories', CategoryController::class);

    Route::resource('/products/docs', DocController::class);



    Route::resource('product/printed-stationeries', PrintedStationeryController::class);
    Route::resource('product/stationery-transactions', StationeryTransactionController::class);
    //Report
    Route::get('reports/printed-stationeries', [ReportController::class, 'printedStationeries'])->name('report.printed-stationeries');


    Route::resource('product/dispatch-registers', DispatchRegisterController::class);




    // ComplaintStatusType Routes
    Route::prefix('complaint-status-types')->name('complaint-status-types.')->group(function () {
        Route::get('/', [ComplaintStatusTypeController::class, 'index'])->name('index');
        Route::get('/create', [ComplaintStatusTypeController::class, 'create'])->name('create');
        Route::post('/', [ComplaintStatusTypeController::class, 'store'])->name('store');
        Route::get('/{complaintStatusType}', [ComplaintStatusTypeController::class, 'show'])->name('show');
        Route::get('/{complaintStatusType}/edit', [ComplaintStatusTypeController::class, 'edit'])->name('edit');
        Route::put('/{complaintStatusType}', [ComplaintStatusTypeController::class, 'update'])->name('update');
        Route::delete('/{complaintStatusType}', [ComplaintStatusTypeController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('auth')->group(function () {
        // Download private files by path
        Route::get('/download/{path}', [DownloadController::class, 'download'])
            ->where('path', '.*')
            ->name('file.download');

        // Stream/view files inline
        Route::get('/view/{path}', [DownloadController::class, 'view'])
            ->where('path', '.*')
            ->name('file.view');
    });

});
