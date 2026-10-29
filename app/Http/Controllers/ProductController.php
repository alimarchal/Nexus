<?php

namespace App\Http\Controllers;

use App\Models\AccountOpeningRequest;
use App\Models\Aksic;
use App\Models\AksicClaim;
use App\Models\FileManagementSystem;
use App\Support\OfficeAccess;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Module cards. Each count is worked out only when the user holds that
     * module's view permission, and follows the same office rules as the module.
     */
    public function product(): View
    {
        $user = auth()->user();

        return view('product.index', [
            'aksicCount' => $user?->can('view aksics')
                ? OfficeAccess::scope(Aksic::query(), $user)->count()
                : 0,
            'aksicClaimCount' => $user?->can('view aksic claims') && Schema::hasTable('aksic_claims')
                ? (OfficeAccess::seesAll($user)
                    ? AksicClaim::query()->count()
                    : AksicClaim::query()->whereHas('items', fn ($items) => OfficeAccess::scope($items, $user))->count())
                : 0,
            'fileManagementSystemCount' => $user?->can('view file management systems')
                ? FileManagementSystem::visibleTo($user)->count()
                : 0,
            // Counted only for users who may see the module, so the page keeps
            // working for everyone else (and before the module is migrated).
            'accountOpeningCount' => $user?->can('view account openings')
                ? AccountOpeningRequest::visibleTo($user)->count()
                : 0,
        ]);
    }
}
