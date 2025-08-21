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
        // Admin bypass - keep existing functionality
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });

        // Use the new reusable permission services
        $registry = PermissionRegistry::create();
        
        // Basic permissions
        $registry
            ->add(SimplePermissionService::create('manage_users'))
            ->add(SimplePermissionService::create('view_records'))
            ->add(SimplePermissionService::create('create_records'))
            ->add(SimplePermissionService::create('edit_records'))
            ->add(SimplePermissionService::create('delete_records'))
            ->add(SimplePermissionService::create('is_admin'));
        
        // Hierarchical location permissions
        $registry
            ->add(new StatePermissionService())
            ->add(new LgaPermissionService())
            ->add(new WardPermissionService());
        
        // Project outlook permissions
        $registry
            ->add(SimplePermissionService::create('view_project_outlooks'))
            ->add(SimplePermissionService::create('create_project_outlooks'))
            ->add(ResourcePermissionService::create(
                'edit_project_outlooks',
                ProjectOutlook::class
            ))
            ->add(ResourcePermissionService::create(
                'delete_project_outlooks', 
                ProjectOutlook::class
            ));
        
        // Register all services
        $registry->register();
    }
}
