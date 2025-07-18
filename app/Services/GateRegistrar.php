<?php
namespace App\Services;

use App\Models\Lga;
use App\Models\ProjectOutlook;
use App\Models\State;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Support\Facades\Gate;

class GateRegistrar
{
    public static function register(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        // Basic user management gates
        Gate::define('manage_users', fn(User $user) => $user->hasPermission('manage_users'));

        // Record management
        Gate::define('view_records', fn(User $user) => $user->hasPermission('view_records'));
        Gate::define('create_records', fn(User $user) => $user->hasPermission('create_records'));
        Gate::define('edit_records', fn(User $user) => $user->hasPermission('edit_records'));
        Gate::define('delete_records', fn(User $user) => $user->hasPermission('delete_records'));

        // State-level permission
        Gate::define('manage_state_records', function (User $user, State $state) {
            return $user->hasPermission('manage_state_records') &&
                   $user->statePermissions->contains($state);
        });

        // LGA-level permission
        Gate::define('manage_lga_records', function (User $user, Lga $lga) {
            return $user->hasPermission('manage_lga_records') &&
                   $user->lgaPermissions->contains($lga);
        });

        // Ward-level permission
        Gate::define('manage_ward_records', function (User $user, Ward $ward) {
            return $user->hasPermission('manage_ward_records') &&
                   (
                       $user->wardPermissions->contains($ward) ||
                       $user->lgaPermissions->contains($ward->lga) ||
                       $user->statePermissions->contains($ward->lga->state)
                   );
        });

        Gate::define('view_project_outlooks', fn(User $user) => $user->hasPermission('view_project_outlooks'));
        Gate::define('create_project_outlooks', fn(User $user) => $user->hasPermission('create_project_outlooks'));
        Gate::define('edit_project_outlooks', function (User $user, ProjectOutlook $outlook = null) {
            return $user->hasPermission('edit_project_outlooks');
        });
        Gate::define('delete_project_outlooks', function (User $user, ProjectOutlook $outlook = null) {
            return $user->hasPermission('delete_project_outlooks');
        });

        // Admin
        Gate::define('is_admin', fn(User $user) => $user->hasRole('admin'));
    }
}
