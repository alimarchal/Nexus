<?php

namespace App\Http\Controllers;

use App\Models\FileManagementSystem;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function product(): View
    {
        return view('product.index', [
            'fileManagementSystemCount' => FileManagementSystem::visibleTo(auth()->user())->count(),
        ]);
    }

    public function branchSetting(): View
    {
        return view('product.daily-positions');
    }
}
