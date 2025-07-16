<?php

namespace App\Providers;

// app/Providers/AuthServiceProvider.php
use App\Models\User;
use App\Models\Record;
use App\Models\State;
use App\Models\Lga;
use App\Models\Ward;
use App\Policies\UserPolicy;
use App\Policies\RecordPolicy;
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
        User::class => UserPolicy::class,
        Record::class => RecordPolicy::class,
        // Add other model-policy mappings as needed
    ];

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for user management
        Gate::define('manage_users', function (User $user) {
            return $user->hasPermission('manage_users');
        });

        // Define gates for record management
        Gate::define('view_records', function (User $user) {
            return $user->hasPermission('view_records');
        });

        Gate::define('create_records', function (User $user) {
            return $user->hasPermission('create_records');
        });

        Gate::define('edit_records', function (User $user) {
            return $user->hasPermission('edit_records');
        });

        Gate::define('delete_records', function (User $user) {
            return $user->hasPermission('delete_records');
        });

        // Define state-level permissions
        Gate::define('manage_state_records', function (User $user, State $state) {
            if ($user->hasPermission('manage_state_records')) {
                return $user->statePermissions->contains($state);
            }
            return false;
        });

        // Define LGA-level permissions
        Gate::define('manage_lga_records', function (User $user, Lga $lga) {
            if ($user->hasPermission('manage_lga_records')) {
                return $user->lgaPermissions->contains($lga);
            }
            return false;
        });

        // Global admin gate
        Gate::define('is_admin', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('manage_ward_records', function (User $user, Ward $ward) {
            if ($user->hasPermission('manage_ward_records')) {
                return $user->wardPermissions->contains($ward) || 
                    $user->lgaPermissions->contains($ward->lga) ||
                    $user->statePermissions->contains($ward->lga->state);
            }
            return false;
        });
    }
}