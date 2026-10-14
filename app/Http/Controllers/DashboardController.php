<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Support\PendingMigrations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Home dashboard: AKSIC, file management and account opening figures for
     * the user's own office (branch, region, ...) or the whole bank. Each
     * section is shown only with its module and dashboard permissions; with
     * no section at all the user is sent to their first module instead.
     */
    public function dashboard(Request $request, DashboardService $dashboard): View|RedirectResponse
    {
        $user = $request->user();

        // No module on the dashboard (or no "view dashboard"): go to the first
        // module the user can open instead. Super admin always sees it.
        if (! $dashboard->isVisibleTo($user)) {
            return redirect()->to($dashboard->landingUrl($user));
        }

        return view('dashboard', [
            'aksic' => $dashboard->aksic($user),
            'files' => $dashboard->files($user),
            'accountOpenings' => $dashboard->accountOpenings($user),
            'greeting' => DashboardService::greeting(),
            // Super admin is warned when the code has migrations the database has not run yet.
            'pendingMigrations' => DashboardService::isSuperAdmin($user) ? PendingMigrations::list() : [],
        ]);
    }
}
