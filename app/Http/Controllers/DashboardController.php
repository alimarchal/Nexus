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

        return $this->render($user, $dashboard, null);
    }

    /**
     * One module's own dashboard (AKSIC, file management or account opening).
     * It needs the module's view permission and its dashboard permission.
     */
    public function section(Request $request, DashboardService $dashboard, string $section): View
    {
        $user = $request->user();
        $key = DashboardService::sectionForSlug($section);

        abort_unless($key !== null && $dashboard->shows($user, $key), 403);

        return $this->render($user, $dashboard, $key);
    }

    private function render($user, DashboardService $dashboard, ?string $only): View
    {
        $wants = fn (string $key) => $only === null || $only === $key;

        return view('dashboard', [
            'aksic' => $wants('aksic') ? $dashboard->aksic($user) : null,
            'files' => $wants('files') ? $dashboard->files($user) : null,
            'accountOpenings' => $wants('account_openings') ? $dashboard->accountOpenings($user) : null,
            'tabs' => $dashboard->tabsFor($user),
            'only' => $only,
            'pageTitle' => $only ? DashboardService::SECTION_PAGES[$only][1] : 'Dashboard',
            'greeting' => DashboardService::greeting(),
            // Super admin is warned when the code has migrations the database has not run yet.
            'pendingMigrations' => DashboardService::isSuperAdmin($user) ? PendingMigrations::list() : [],
        ]);
    }
}
