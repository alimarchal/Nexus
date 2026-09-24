<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view dashboard', only: ['dashboard']),
            new Middleware('role_or_permission:view analytics', only: ['daily_position']),
        ];
    }

    /**
     * Home dashboard: AKSIC and file management figures for the user's own
     * office (branch, region, ...) or the whole bank, by role and permission.
     */
    public function dashboard(Request $request, DashboardService $dashboard): View
    {
        $user = $request->user();

        abort_unless(
            $user->is_super_admin === 'Yes' || $user->hasAnyRole(['branch', 'region', 'division', 'head-office', 'super-admin']),
            403
        );

        return view('dashboard', [
            'aksic' => $dashboard->aksic($user),
            'files' => $dashboard->files($user),
            'greeting' => DashboardService::greeting(),
        ]);
    }

    public function daily_position(Request $request): View
    {
        return view('dashboard.daily-position');
    }
}
