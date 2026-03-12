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
        // Using strtolower makes this "Admin" vs "admin" proof.
        Gate::before(function (User $user) {
            if (strtolower($user->role) === 'Admin') {
                return true;
            }
        });

        // 2. Manage Inventory (Create, Edit, Delete, Stock In/Out)
        Gate::define('manage-inventory', function (User $user) {
            $role = strtolower($user->role);
            return in_array($role, ['Admin', 'Clerk', 'Custodian']);
        });

        // 3. User Management
        // If the user isn't an Admin (caught by before), they are denied.
        Gate::define('manage-users', function (User $user) {
            return false; 
        });

        // 4. View Only Access
        Gate::define('view-inventory', function (User $user) {
            $role = strtolower($user->role);
            return in_array($role, ['Admin', 'Clerk', 'Custodian', 'Viewer']);
        });
    }
}