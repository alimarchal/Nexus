<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view dashboard', only: ['dashboard']),
            new Middleware('role_or_permission:view analytics', only: ['daily_position']),
        ];
    }

    public function dashboard()
    {

        $user = auth()->user();

        // Check if user has any valid dashboard role
        if (!$user->hasAnyRole(['branch', 'region', 'division', 'head-office', 'super-admin'])) {
            abort(403); // Forbidden if no valid role
        }
        // Route to role-specific dashboard view
        return match ($user->roles->first()->name) {
            'branch' => view('dashboard.branches'),
            'region' => view('dashboard.region'),
            'division' => view('dashboard.division'),
            'head-office' => view('dashboard.all'),
            'super-admin' => view('dashboard.dashboard'),
            default => abort(403)
        };

//        return view('dashboard');
    }

    public function daily_position(Request $request)
    {
        return view('dashboard.daily-position');
    }
}
