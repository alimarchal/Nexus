<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserModuleController extends Controller
{
    public function index()
    {
        return view('settings.user-module'); // Make sure the correct view is returned
    }
}


