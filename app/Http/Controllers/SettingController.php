<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:view settings', only: ['index', 'branchSetting', 'userModule']),
            new Middleware('role_or_permission:edit settings', only: ['update']),
            new Middleware('role_or_permission:system settings', only: ['system']),
        ];
    }

    /**
     * Display the settings index page.
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Display the branch settings page.
     */
    public function branchSetting()
    {
        return view('settings.branchsetting'); // Render the branch settings view
    }
    public function userModule()
    {
        return view('settings.user-module'); // Render the branch settings view
    }
}