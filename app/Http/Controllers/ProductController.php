<?php

namespace App\Http\Controllers;

use App\Models\AccountOpeningRequest;
use App\Models\FileManagementSystem;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function product(): View
    {
        return view('product.index', [
            'fileManagementSystemCount' => FileManagementSystem::visibleTo(auth()->user())->count(),
            // Counted only for users who may see the module, so the page keeps
            // working for everyone else (and before the module is migrated).
            'accountOpeningCount' => auth()->user()?->can('view account openings')
                ? AccountOpeningRequest::visibleTo(auth()->user())->count()
                : 0,
        ]);
    }

    public function branchSetting(): View
    {
        return view('product.daily-positions');
    }
}
