<?php

use App\Http\Controllers\AccountOpeningController;
use App\Http\Controllers\AksicApplicationController;
use App\Http\Controllers\AksicBudgetController;
use App\Http\Controllers\AksicClaimController;
use App\Http\Controllers\AksicController;
use App\Http\Controllers\AksicRuleController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchTargetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintAttachmentController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ComplaintStatusTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EmployeeResourceController;
use App\Http\Controllers\FileCategoryController;
use App\Http\Controllers\FileManagementSystemController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\PaperAccountOpeningController;
use App\Http\Controllers\PaperAccountOpeningUploadController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserModuleController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('login'); // Redirect to the login route
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('product', [ProductController::class, 'product'])->name('product.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/branch', [SettingController::class, 'branchSetting'])->name('settings.branchsetting');
    Route::resource('/settings/branch-targets', BranchTargetController::class);
    Route::resource('/settings/branch/branches', BranchController::class);
    Route::resource('/settings/branch/regions', RegionController::class);
    Route::resource('/settings/branch/districts', DistrictController::class);
    Route::resource('/settings/roles', RoleController::class);
    Route::resource('/settings/permissions', PermissionController::class)->middleware('auth');
    Route::post('/settings/users/bulk-update', [UserController::class, 'bulkUpdate'])->name('users.bulk-update');
    Route::resource('/settings/users', UserController::class);

    // Updated route to point to the ReportController's method
    Route::get('reports/deposit-advances-reports-branch', [ReportController::class, 'depositadvancesregionPositionReport'])->name('reports.deposit-advances-reports-branch');
    Route::get('reports/deposit-advances-reports-region', [ReportController::class, 'depositadvancesPositionReport'])->name('reports.deposit-advances-reports-region');
    Route::get('reports/accounts-branchwise-reports', [ReportController::class, 'accountsbranchwisePositionReport'])->name('reports.accounts-branchwise-reports');
    Route::get('reports/aksic-rules-report', [ReportController::class, 'aksicRulesReport'])->name('reports.aksic-rules-report');
    Route::get('settings/user-module', [UserModuleController::class, 'index'])->name('user.module');
    Route::get('settings/user-module/users', [UserController::class, 'index'])->name('users.index');
    Route::get('settings/user-module/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('settings/user-module/roles', [RoleController::class, 'index'])->name('roles.index');

    Route::get('reports/accounts-regionwise-reports', [ReportController::class, 'accountsregionwisePositionReport'])->name('reports.accounts-regionwise-reports');
    Route::get('/products/aksic-2025', [AksicApplicationController::class, 'index'])->name('aksic-applications.index');
    Route::post('/products/aksic-2025/sync', [AksicApplicationController::class, 'syncApplications'])->name('aksic-applications.sync');
    Route::get('/products/aksic-2025/{aksicApplication}', [AksicApplicationController::class, 'show'])->name('aksic-applications.show');
    Route::get('/products/aksic-2025/{aksicApplication}/pdf', [AksicApplicationController::class, 'downloadPdf'])->name('aksic-applications.pdf');
    Route::post('/products/aksic-2025/{aksicApplication}/status-update', [AksicApplicationController::class, 'updateStatus'])->name('aksic-applications.update-status');
    // Route::resource('/products/complaints', ComplaintController::class);
    // Additional complaint-specific routes (placed BEFORE resource to avoid conflicts with {complaint} wildcard)
    Route::prefix('products/complaints')->name('complaints.')->group(function () {
        // Analytics and reporting (must come before resource show route so 'analytics' isn't treated as an ID)
        Route::get('analytics', [ComplaintController::class, 'analytics'])->name('analytics');
        Route::get('analytics-data', [ComplaintController::class, 'analyticsData'])->name('analytics-data');
        // Export functionality
        Route::get('export', [ComplaintController::class, 'export'])->name('export');
        // Full JSON data snapshot for a single complaint (all related tables)
        Route::get('{complaint}/full', [ComplaintController::class, 'fullData'])->name('full');
        // Bulk operations
        Route::post('bulk-update-status', [ComplaintController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::post('bulk-assign', [ComplaintController::class, 'bulkAssign'])->name('bulk-assign');
        Route::post('bulk-update', [ComplaintController::class, 'bulkUpdate'])->name('bulk-update');
        // Attachments (dedicated upload endpoint)
        Route::post('{complaint}/attachments', [ComplaintController::class, 'addAttachments'])->name('add-attachments');
        Route::get('attachments/{attachment}/download', [ComplaintController::class, 'downloadAttachment'])->name('download-attachment');
        Route::delete('attachments/{attachment}', [ComplaintController::class, 'deleteAttachment'])->name('delete-attachment');
        // Comment management
        Route::post('{complaint}/comments', [ComplaintController::class, 'addComment'])->name('add-comment');
        // Escalation management
        Route::post('{complaint}/escalate', [ComplaintController::class, 'escalate'])->name('escalate');
        // Watcher management
        Route::post('{complaint}/watchers', [ComplaintController::class, 'updateWatchers'])->name('update-watchers');
        // Customer satisfaction
        Route::post('{complaint}/satisfaction', [ComplaintController::class, 'updateSatisfactionScore'])->name('update-satisfaction');
    });

    // Core complaints resource with numeric constraint to prevent capturing 'analytics'
    Route::resource('products/complaints', ComplaintController::class); // allow UUIDs

    Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.update-status');
    Route::get('/complaints/attachments/{attachment}/download', [ComplaintAttachmentController::class, 'download'])->name('complaints.attachments.download');
    Route::resource('settings/user-module/managers', ManagerController::class);

    Route::resource('/settings/categories', CategoryController::class);
    Route::resource('/settings/file-categories', FileCategoryController::class)->except(['show']);
    Route::post('/product/file-management-systems/{fileManagementSystem}/submit', [FileManagementSystemController::class, 'submit'])->name('file-management-systems.submit');
    Route::post('/product/file-management-systems/{fileManagementSystem}/approve', [FileManagementSystemController::class, 'approve'])->name('file-management-systems.approve');
    Route::post('/product/file-management-systems/{fileManagementSystem}/reject', [FileManagementSystemController::class, 'reject'])->name('file-management-systems.reject');
    Route::post('/product/file-management-systems/{fileManagementSystem}/unlock', [FileManagementSystemController::class, 'unlock'])->name('file-management-systems.unlock');
    Route::get('/product/file-management-systems/{fileManagementSystem}/pages/{media}', [FileManagementSystemController::class, 'page'])->name('file-management-systems.pages.show');
    Route::get('/product/file-management-systems/{fileManagementSystem}/transfer', [FileManagementSystemController::class, 'transfer'])->name('file-management-systems.transfer');
    Route::post('/product/file-management-systems/{fileManagementSystem}/transfer', [FileManagementSystemController::class, 'storeTransfer'])->name('file-management-systems.transfers.store');
    Route::patch('/product/file-management-systems/{fileManagementSystem}/transfers/{transfer}', [FileManagementSystemController::class, 'decideTransfer'])->name('file-management-systems.transfers.decide');
    Route::get('/product/file-management-systems/{fileManagementSystem}/archive', [FileManagementSystemController::class, 'archiveForm'])->name('file-management-systems.archive-form');
    Route::post('/product/file-management-systems/{fileManagementSystem}/archive', [FileManagementSystemController::class, 'storeArchive'])->name('file-management-systems.archive');
    Route::post('/product/file-management-systems/{fileManagementSystem}/unarchive', [FileManagementSystemController::class, 'removeFromBox'])->name('file-management-systems.unarchive');
    Route::get('/product/boxes', [FileManagementSystemController::class, 'boxesList'])->name('file-management-systems.boxes');
    Route::get('/product/boxes/create', [FileManagementSystemController::class, 'createBox'])->name('file-management-systems.boxes.create');
    Route::post('/product/boxes', [FileManagementSystemController::class, 'storeBox'])->name('file-management-systems.boxes.store');
    Route::match(['get', 'post'], '/product/file-management-systems/export/csv', [FileManagementSystemController::class, 'exportCsv'])->name('file-management-systems.export-csv');
    Route::match(['get', 'post'], '/product/file-management-systems/export/zip', [FileManagementSystemController::class, 'exportZip'])->name('file-management-systems.export-zip');
    Route::resource('/product/file-management-systems', FileManagementSystemController::class);
    Route::delete('/product/file-management-systems/{fileManagementSystem}/media/{media}', [FileManagementSystemController::class, 'destroyMedia'])->name('file-management-systems.media.destroy');

    // BAJK Account Opening Form (AOF) -- one wizard for both printed forms
    // (Individual/Joint/Sole Proprietor and Government/Company/NGO).
    Route::prefix('product/account-openings')->name('account-openings.')->group(function () {
        Route::get('/', [AccountOpeningController::class, 'index'])->name('index');
        Route::get('/create', [AccountOpeningController::class, 'create'])->name('create');
        Route::post('/', [AccountOpeningController::class, 'store'])->name('store');
        Route::get('/customers/search', [AccountOpeningController::class, 'searchCustomers'])->name('customers.search');
        Route::get('/{accountOpeningRequest}', [AccountOpeningController::class, 'show'])->name('show');
        Route::get('/{accountOpeningRequest}/print', [AccountOpeningController::class, 'print'])->name('print');
        Route::get('/{accountOpeningRequest}/paper', [PaperAccountOpeningController::class, 'edit'])->name('paper.edit');
        Route::put('/{accountOpeningRequest}/paper', [PaperAccountOpeningController::class, 'update'])->name('paper.update');
        Route::post('/{accountOpeningRequest}/paper/uploads', [PaperAccountOpeningUploadController::class, 'store'])->name('paper.uploads.store');
        Route::get('/{accountOpeningRequest}/paper/uploads/{media}', [PaperAccountOpeningUploadController::class, 'show'])->name('paper.uploads.show');
        Route::delete('/{accountOpeningRequest}/paper/uploads/{media}', [PaperAccountOpeningUploadController::class, 'destroy'])->name('paper.uploads.destroy');
        Route::get('/{accountOpeningRequest}/steps/{step}', [AccountOpeningController::class, 'editStep'])->name('steps.edit');
        Route::put('/{accountOpeningRequest}/steps/{step}', [AccountOpeningController::class, 'updateStep'])->name('steps.update');
        Route::post('/{accountOpeningRequest}/submit', [AccountOpeningController::class, 'submit'])->name('submit');
        Route::post('/{accountOpeningRequest}/approve', [AccountOpeningController::class, 'approve'])->name('approve');
        Route::post('/{accountOpeningRequest}/reject', [AccountOpeningController::class, 'reject'])->name('reject');
        Route::post('/{accountOpeningRequest}/unlock', [AccountOpeningController::class, 'unlock'])->name('unlock');
        Route::delete('/{accountOpeningRequest}', [AccountOpeningController::class, 'destroy'])->name('destroy');
    });

    Route::get('product/aksic-template', [AksicController::class, 'downloadTemplate'])->name('aksic.template');
    Route::post('product/aksic-import', [AksicController::class, 'import'])->name('aksic.import');
    Route::post('product/aksic/{aksic}/approve', [AksicController::class, 'approve'])->name('aksic.approve');
    // AKSIC markup budget (allocation sheet): position, versions, enhance/reduce with ledger.
    Route::prefix('product/aksic-budgets')->name('aksic-budgets.')->group(function () {
        Route::get('/', [AksicBudgetController::class, 'index'])->name('index');
        Route::get('/create', [AksicBudgetController::class, 'create'])->name('create');
        Route::post('/', [AksicBudgetController::class, 'store'])->name('store');
        Route::get('/{aksicBudget}', [AksicBudgetController::class, 'show'])->name('show');
        Route::get('/{aksicBudget}/edit', [AksicBudgetController::class, 'edit'])->name('edit');
        Route::put('/{aksicBudget}', [AksicBudgetController::class, 'update'])->name('update');
        Route::post('/{aksicBudget}/activate', [AksicBudgetController::class, 'activate'])->name('activate');
        Route::post('/{aksicBudget}/redistribute', [AksicBudgetController::class, 'redistribute'])->name('redistribute');
        Route::post('/{aksicBudget}/allocations/{allocation}/revise', [AksicBudgetController::class, 'revise'])->name('revise');
    });

    // AKSIC claim lodging + District / Region / Branch / Gender MIS (Portal Change #5).
    Route::prefix('product/aksic-claims')->name('aksic-claims.')->group(function () {
        Route::get('/', [AksicClaimController::class, 'index'])->name('index');
        Route::get('/create', [AksicClaimController::class, 'create'])->name('create');
        Route::post('/', [AksicClaimController::class, 'store'])->name('store');
        Route::get('/{aksicClaim}', [AksicClaimController::class, 'show'])->name('show');
        Route::post('/{aksicClaim}/status', [AksicClaimController::class, 'updateStatus'])->name('status');
        Route::delete('/{aksicClaim}', [AksicClaimController::class, 'destroy'])->name('destroy');
    });
    // Printable AKSIC loan case sheet + repayment schedule (standalone document view).
    Route::get('product/aksic-export', [AksicController::class, 'export'])->name('aksic.export');
    Route::post('product/aksic-demo-data', [AksicController::class, 'demoData'])->name('aksic.demo-data'); // local only
    Route::get('product/aksic/{aksic}/print', [AksicController::class, 'print'])->name('aksic.print');
    Route::resource('product/aksic', AksicController::class)->names('aksic');
    Route::resource('product/aksic-rules', AksicRuleController::class)->parameters(['aksic-rules' => 'aksic_rule'])->names('aksic-rules');
    // Employee Resources
    Route::resource('products/employee-resources', EmployeeResourceController::class)->names('employee_resources');
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
        Route::get('/download/{path}', [DownloadController::class, 'download'])->where('path', '.*')->name('file.download');
        // Stream/view files inline
        Route::get('/view/{path}', [DownloadController::class, 'view'])->where('path', '.*')->name('file.view');
    });

});

// TEMPORARY one-off route to run config:clear, migrate, storage:link and optimize:clear from the browser on a server without shell
// access. It answers 404 unless RUN_ARTISAN_KEY is set in .env and the key in the URL matches. Remove this route, the
// 'artisan_runner_key' line in config/app.php and RUN_ARTISAN_KEY from .env once it has been used.
Route::get('/run-artisan/{key}', function (string $key) {
    $expected = (string) config('app.artisan_runner_key');

    abort_unless($expected !== '' && hash_equals($expected, $key), 404);

    set_time_limit(0);
    $output = [];

    foreach (['config:clear', 'migrate --force', 'storage:link --force', 'config:clear', 'optimize:clear'] as $command) {
        Artisan::call($command);
        $output[] = '$ php artisan '.$command."\n".Artisan::output();
    }

    return response(implode("\n", $output), 200, ['Content-Type' => 'text/plain']);
});
