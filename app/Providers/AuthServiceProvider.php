<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Branch;
use App\Policies\AreaPolicy;
use App\Policies\BranchPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Area::class => AreaPolicy::class,
        Branch::class => BranchPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
