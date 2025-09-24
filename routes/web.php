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
use App\Http\Controllers\AksicApplicationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserModuleController;
use App\Http\Controllers\BranchTargetController;
use App\Http\Controllers\DailyPositionController;
use App\Http\Controllers\DispatchRegisterController;
use App\Http\Controllers\PrintedStationeryController;
use App\Http\Controllers\ComplaintAttachmentController;
use App\Http\Controllers\ComplaintStatusTypeController;
use App\Http\Controllers\StationeryTransactionController;
use App\Http\Controllers\EmployeeResourceController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuditExtraController;







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
    Route::get('/products/aksic-2025', [AksicApplicationController::class, 'index'])->name('aksic-applications.index');
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

    Route::resource('product/printed-stationeries', PrintedStationeryController::class);
    Route::resource('product/stationery-transactions', StationeryTransactionController::class);
    Route::get('product/stationery-transactions/{stationeryTransaction}/download', [StationeryTransactionController::class, 'downloadDocument'])->name('stationery-transactions.download');
    //Report
    Route::get('reports/printed-stationeries', [ReportController::class, 'printedStationeries'])->name('report.printed-stationeries');


    Route::resource('product/dispatch-registers', DispatchRegisterController::class);


    // Employee Resources
    Route::resource('products/employee-resources', EmployeeResourceController::class)->names('employee_resources');
    // Audits module routes
    Route::resource('products/audits', AuditController::class)->names('audits');
    Route::prefix('products/audits/{audit}')->name('audits.')->group(function () {
        // Full JSON snapshot (must be before other parameterized routes used by export button)
        Route::get('full', [\App\Http\Controllers\AuditController::class, 'fullData'])->name('full');
        // Basic Audit Updates
        Route::patch('basic-info', [AuditExtraController::class, 'updateBasicInfo'])->name('update-basic-info');
        Route::patch('status', [AuditExtraController::class, 'updateStatus'])->name('update-status');

        // Document Management
        Route::post('documents', [AuditExtraController::class, 'addDocument'])->name('documents.store');
        Route::patch('documents/{document}', [AuditExtraController::class, 'updateDocument'])->name('documents.update');
        Route::delete('documents/{document}', [AuditExtraController::class, 'deleteDocument'])->name('documents.delete');
        Route::get('documents/{document}/download', [AuditExtraController::class, 'downloadDocument'])->name('documents.download');

        // Checklist Management
        Route::post('checklist-items', [AuditExtraController::class, 'addChecklistItem'])->name('checklist.add');
        Route::patch('checklist-items/{item}', [AuditExtraController::class, 'updateChecklistItem'])->name('checklist.update');
        Route::delete('checklist-items/{item}', [AuditExtraController::class, 'deleteChecklistItem'])->name('checklist.delete');
        Route::post('responses', [AuditExtraController::class, 'saveResponses'])->name('save-responses');
        // Inline Assessment Items (audit-specific)
        Route::post('inline-items', [AuditExtraController::class, 'addInlineChecklistItem'])->name('inline-items.add');
        Route::delete('inline-items/{item}', [AuditExtraController::class, 'deleteInlineChecklistItem'])->name('inline-items.delete');

        // Risk Management
        Route::post('risks', [AuditExtraController::class, 'addRisk'])->name('risks.add');
        Route::patch('risks/{risk}', [AuditExtraController::class, 'updateRisk'])->name('risks.update');
        Route::delete('risks/{risk}', [AuditExtraController::class, 'deleteRisk'])->name('risks.delete');

        // Findings & Actions
        Route::post('findings', [AuditExtraController::class, 'addFinding'])->name('findings.add');
        Route::patch('findings/{finding}', [AuditExtraController::class, 'updateFinding'])->name('findings.update');
        Route::delete('findings/{finding}', [AuditExtraController::class, 'deleteFinding'])->name('findings.delete');
        Route::post('findings/{finding}/actions', [AuditExtraController::class, 'addAction'])->name('actions.add');
        Route::patch('actions/{action}', [AuditExtraController::class, 'updateAction'])->name('actions.update');
        Route::delete('actions/{action}', [AuditExtraController::class, 'deleteAction'])->name('actions.delete');
        Route::post('actions/{action}/updates', [AuditExtraController::class, 'addActionUpdate'])->name('actions.updates.add');

        // Team & Scope Management
        Route::post('auditors', [AuditExtraController::class, 'assignAuditors'])->name('assign-auditors');
        Route::patch('auditors/{auditor}', [AuditExtraController::class, 'updateAuditor'])->name('auditors.update');
        Route::delete('auditors/{auditor}', [AuditExtraController::class, 'removeAuditor'])->name('auditors.delete');
        Route::post('scopes', [AuditExtraController::class, 'addScope'])->name('scopes.add');
        Route::patch('scopes/{scope}', [AuditExtraController::class, 'updateScope'])->name('scopes.update');
        Route::delete('scopes/{scope}', [AuditExtraController::class, 'deleteScope'])->name('scopes.delete');

        // Schedule Management
        Route::post('schedules', [AuditExtraController::class, 'addSchedule'])->name('schedules.add');
        Route::patch('schedules/{schedule}', [AuditExtraController::class, 'updateSchedule'])->name('schedules.update');
        Route::delete('schedules/{schedule}', [AuditExtraController::class, 'deleteSchedule'])->name('schedules.delete');

        // Notifications Management
        Route::post('notifications', [AuditExtraController::class, 'addNotification'])->name('notifications.add');
        Route::patch('notifications/{notification}', [AuditExtraController::class, 'updateNotification'])->name('notifications.update');
        Route::delete('notifications/{notification}', [AuditExtraController::class, 'deleteNotification'])->name('notifications.delete');
        Route::post('notifications/{notification}/resend', [AuditExtraController::class, 'resendNotification'])->name('notifications.resend');

        // Metrics & Analytics
        Route::post('metrics/recalc', [AuditExtraController::class, 'recalcMetrics'])->name('metrics.recalc');
        Route::post('metrics', [AuditExtraController::class, 'addMetric'])->name('metrics.add');
        Route::patch('metrics/{metric}', [AuditExtraController::class, 'updateMetric'])->name('metrics.update');
        Route::delete('metrics/{metric}', [AuditExtraController::class, 'deleteMetric'])->name('metrics.delete');

        // Tags Management
        Route::post('tags', [AuditExtraController::class, 'addTag'])->name('tags.add');
        Route::delete('tags/{tag}', [AuditExtraController::class, 'removeTag'])->name('tags.remove');

        // Audit Types Management
        Route::post('types', [AuditExtraController::class, 'updateType'])->name('types.update');

        // Finding Attachments
        Route::post('findings/{finding}/attachments', [AuditExtraController::class, 'addFindingAttachment'])->name('findings.attachments.add');
        Route::delete('findings/{finding}/attachments/{attachment}', [AuditExtraController::class, 'deleteFindingAttachment'])->name('findings.attachments.delete');
        Route::get('findings/{finding}/attachments/{attachment}/download', [AuditExtraController::class, 'downloadFindingAttachment'])->name('findings.attachments.download');

        // Status History (readonly - auto-generated)
        Route::get('status-history', [AuditExtraController::class, 'getStatusHistory'])->name('status-history');
    });

    // Global Audit Management Routes (not audit-specific)
    Route::prefix('audit-management')->name('audit-management.')->group(function () {
        // Audit Types Management
        Route::resource('types', \App\Http\Controllers\AuditTypeController::class)->names('audit-types');

        // Global Audit Tags Management  
        Route::resource('tags', \App\Http\Controllers\AuditTagController::class)->names('audit-tags');

        // Audit Tag Pivots (for assigning tags to audits)
        Route::post('audits/{audit}/tags', [\App\Http\Controllers\AuditTagPivotController::class, 'store'])->name('audit-tag-pivots.store');
        Route::delete('audits/{audit}/tags/{tag}', [\App\Http\Controllers\AuditTagPivotController::class, 'destroy'])->name('audit-tag-pivots.destroy');

        // Audit Schedules Management
        Route::post('audits/{audit}/schedules', [\App\Http\Controllers\AuditScheduleController::class, 'store'])->name('audit-schedules.store');
        Route::patch('audits/{audit}/schedules/{schedule}', [\App\Http\Controllers\AuditScheduleController::class, 'update'])->name('audit-schedules.update');
        Route::delete('audits/{audit}/schedules/{schedule}', [\App\Http\Controllers\AuditScheduleController::class, 'destroy'])->name('audit-schedules.destroy');

        // Audit Notifications Management
        Route::post('audits/{audit}/notifications', [\App\Http\Controllers\AuditNotificationController::class, 'store'])->name('audit-notifications.store');
        Route::patch('audits/{audit}/notifications/{notification}', [\App\Http\Controllers\AuditNotificationController::class, 'update'])->name('audit-notifications.update');
        Route::delete('audits/{audit}/notifications/{notification}', [\App\Http\Controllers\AuditNotificationController::class, 'destroy'])->name('audit-notifications.destroy');
        Route::post('audits/{audit}/notifications/{notification}/resend', [\App\Http\Controllers\AuditNotificationController::class, 'resend'])->name('audit-notifications.resend');

        // Audit Action Updates Management
        Route::post('audits/{audit}/actions/{action}/updates', [\App\Http\Controllers\AuditActionUpdateController::class, 'store'])->name('audit-action-updates.store');
        Route::patch('audits/{audit}/actions/{action}/updates/{update}', [\App\Http\Controllers\AuditActionUpdateController::class, 'update'])->name('audit-action-updates.update');
        Route::delete('audits/{audit}/actions/{action}/updates/{update}', [\App\Http\Controllers\AuditActionUpdateController::class, 'destroy'])->name('audit-action-updates.destroy');

        // Audit Finding Attachments Management
        Route::post('audits/{audit}/findings/{finding}/attachments', [\App\Http\Controllers\AuditFindingAttachmentController::class, 'store'])->name('audit-finding-attachments.store');
        Route::delete('audits/{audit}/findings/{finding}/attachments/{attachment}', [\App\Http\Controllers\AuditFindingAttachmentController::class, 'destroy'])->name('audit-finding-attachments.destroy');
        Route::get('audits/{audit}/findings/{finding}/attachments/{attachment}/download', [\App\Http\Controllers\AuditFindingAttachmentController::class, 'download'])->name('audit-finding-attachments.download');

        // Audit Metrics Cache Management
        Route::post('audits/{audit}/metrics-cache/recalculate', [\App\Http\Controllers\AuditMetricsCacheController::class, 'recalculate'])->name('audit-metrics-cache.recalculate');
        Route::delete('audits/{audit}/metrics-cache', [\App\Http\Controllers\AuditMetricsCacheController::class, 'clear'])->name('audit-metrics-cache.clear');
    });

    // Add route aliases for backward compatibility
    Route::name('audit-types.')->group(function () {
        Route::post('audit-types', [\App\Http\Controllers\AuditTypeController::class, 'store'])->name('store');
        Route::delete('audit-types/{type}', [\App\Http\Controllers\AuditTypeController::class, 'destroy'])->name('destroy');
    });

    Route::name('audit-tags.')->group(function () {
        Route::post('audit-tags', [\App\Http\Controllers\AuditTagController::class, 'store'])->name('store');
        Route::delete('audit-tags/{tag}', [\App\Http\Controllers\AuditTagController::class, 'destroy'])->name('destroy');
    });

    Route::name('audit-tag-pivots.')->group(function () {
        Route::post('audits/{audit}/tag-pivots', [\App\Http\Controllers\AuditTagPivotController::class, 'store'])->name('store');
        Route::delete('audits/{audit}/tag-pivots/{tag}', [\App\Http\Controllers\AuditTagPivotController::class, 'destroy'])->name('destroy');
    });



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
