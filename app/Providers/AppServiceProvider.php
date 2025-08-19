<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
        // Paint management permissions
        Gate::define('manage-paints', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-paints', function (User $user) {
            // All authenticated users can view paints
            return true;
        });

        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });

        // User group management (admin only)
        Gate::define('manage-user-groups', function (User $user) {
            return $user->isAdmin();
        });

        // Project management (for future use)
        Gate::define('manage-projects', function (User $user) {
            return $user->isAdmin() || $user->userGroups()->exists();
        });
    }
}