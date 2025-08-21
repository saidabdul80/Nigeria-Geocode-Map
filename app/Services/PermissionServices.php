<?php

namespace App\Services;

use App\Models\Lga;
use App\Models\State;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Support\Facades\Gate;

/**
 * Abstract permission service for reusable permission logic
 */
abstract class AbstractPermissionService
{
    /**
     * Define the permission name
     */
    abstract protected function getPermissionName(): string;
    
    /**
     * Define the resource model class
     */
    abstract protected function getResourceClass(): string;
    
    /**
     * Check if user has the basic permission
     */
    protected function hasBasicPermission(User $user): bool
    {
        return $user->hasPermission($this->getPermissionName());
    }
    
    /**
     * Check hierarchical permissions (ward -> lga -> state)
     */
    protected function hasHierarchicalPermission(User $user, $resource): bool
    {
        $resourceClass = $this->getResourceClass();
        
        if (!$resource instanceof $resourceClass) {
            return false;
        }
        
        // Check direct permission for the resource type
        if ($resource instanceof Ward) {
            return $user->wardPermissions->contains($resource) ||
                   $user->lgaPermissions->contains($resource->lga) ||
                   $user->statePermissions->contains($resource->lga->state);
        }
        
        if ($resource instanceof Lga) {
            return $user->lgaPermissions->contains($resource) ||
                   $user->statePermissions->contains($resource->state);
        }
        
        if ($resource instanceof State) {
            return $user->statePermissions->contains($resource);
        }
        
        return false;
    }
    
    /**
     * Register the gate definition
     */
    public function register(): void
    {
        Gate::define($this->getPermissionName(), function (User $user, $resource = null) {
            if (!$this->hasBasicPermission($user)) {
                return false;
            }
            
            if ($resource === null) {
                return true;
            }
            
            return $this->hasHierarchicalPermission($user, $resource);
        });
    }
}

/**
 * State-level permission service
 */
class StatePermissionService extends AbstractPermissionService
{
    protected function getPermissionName(): string
    {
        return 'manage_state_records';
    }
    
    protected function getResourceClass(): string
    {
        return State::class;
    }
}

/**
 * LGA-level permission service
 */
class LgaPermissionService extends AbstractPermissionService
{
    protected function getPermissionName(): string
    {
        return 'manage_lga_records';
    }
    
    protected function getResourceClass(): string
    {
        return Lga::class;
    }
}

/**
 * Ward-level permission service
 */
class WardPermissionService extends AbstractPermissionService
{
    protected function getPermissionName(): string
    {
        return 'manage_ward_records';
    }
    
    protected function getResourceClass(): string
    {
        return Ward::class;
    }
}

/**
 * Simple permission service for basic permissions
 */
class SimplePermissionService
{
    protected string $permission;
    
    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }
    
    public function register(): void
    {
        Gate::define($this->permission, function (User $user) {
            return $user->hasPermission($this->permission);
        });
    }
    
    /**
     * Create a simple permission service
     */
    public static function create(string $permission): self
    {
        return new self($permission);
    }
}

/**
 * Resource-based permission service with optional owner check
 */
class ResourcePermissionService
{
    protected string $permission;
    protected string $resourceClass;
    protected ?string $ownerField;
    
    public function __construct(string $permission, string $resourceClass, ?string $ownerField = null)
    {
        $this->permission = $permission;
        $this->resourceClass = $resourceClass;
        $this->ownerField = $ownerField;
    }
    
    public function register(): void
    {
        Gate::define($this->permission, function (User $user, $resource = null) {
            if (!$user->hasPermission($this->permission)) {
                return false;
            }
            
            if ($resource === null) {
                return true;
            }
            
            if (!$resource instanceof $this->resourceClass) {
                return false;
            }
            
            // Check ownership if owner field is specified
            if ($this->ownerField && isset($resource->{$this->ownerField})) {
                return $resource->{$this->ownerField} === $user->id;
            }
            
            return true;
        });
    }
    
    /**
     * Create a resource-based permission service
     */
    public static function create(string $permission, string $resourceClass, ?string $ownerField = null): self
    {
        return new self($permission, $resourceClass, $ownerField);
    }
}

/**
 * Permission registry for managing all permission services
 */
class PermissionRegistry
{
    protected array $services = [];
    
    /**
     * Add a permission service
     */
    public function add(object $service): self
    {
        $this->services[] = $service;
        return $this;
    }
    
    /**
     * Register all permission services
     */
    public function register(): void
    {
        foreach ($this->services as $service) {
            if (method_exists($service, 'register')) {
                $service->register();
            }
        }
    }
    
    /**
     * Create a new registry instance
     */
    public static function create(): self
    {
        return new self();
    }
}