<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Superuser: Admin
        // This grants total access regardless of other gates.
        Gate::before(function (User $user) {
            if (strtolower($user->role) === 'admin') {
                return true;
            }
        });

        // 2. Manage Inventory (Create, Edit, Stock In/Out)
        Gate::define('manage-inventory', function (User $user) {
            $role = strtolower($user->role);
            return in_array($role, ['admin', 'clerk', 'custodian']);
        });

        // 3. Delete Inventory
        // Separated this so you can limit deletion to just Admins or Custodians.
        Gate::define('delete-inventory', function (User $user) {
            $role = strtolower($user->role);
            return in_array($role, ['admin', 'custodian']);
        });

        // 4. User Management
        Gate::define('manage-users', function (User $user) {
            return false; // Handled by Gate::before for Admins
        });

        // 5. View Only Access
        Gate::define('view-inventory', function (User $user) {
            $role = strtolower($user->role);
            return in_array($role, ['admin', 'clerk', 'custodian', 'viewer']);
        });
    }
}