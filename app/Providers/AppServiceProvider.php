<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Admin: Full Access (Superuser)
        Gate::before(function (User $user) {
            if ($user->role === 'Admin') {
                return true;
            }
        });

        // 2. Manage Inventory (Create/Edit)
        // Allowed: Admin, Clerk, Custodian
        Gate::define('manage-inventory', function (User $user) {
            return in_array($user->role, ['Clerk', 'Custodian']);
        });

        // 3. Delete Authority
        // Allowed: Admin, Custodian
        Gate::define('delete-inventory', function (User $user) {
            return $user->role === 'Custodian';
        });

        // 4. View Only
        // Allowed: Everyone
        Gate::define('view-inventory', function (User $user) {
            return in_array($user->role, ['Admin', 'Clerk', 'Custodian', 'Viewer']);
        });
    }
}
