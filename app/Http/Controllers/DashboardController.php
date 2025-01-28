<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
}
