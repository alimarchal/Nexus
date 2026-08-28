<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Division;
use App\Models\HeadOffice;
use App\Models\Region;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // Short, stable morph aliases for the polymorphic "fileable" scope on file_management_systems.
        // Plain morphMap (not enforceMorphMap) since other unrelated morph relations
        // in the app (e.g. Spatie Permission's model_has_roles) are not registered here.
        Relation::morphMap([
            'branch' => Branch::class,
            'region' => Region::class,
            'division' => Division::class,
            'head-office' => HeadOffice::class,
        ]);
    }
}
