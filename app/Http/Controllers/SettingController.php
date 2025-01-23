<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
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
}