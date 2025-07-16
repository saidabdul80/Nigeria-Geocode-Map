<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;


    protected $with = ['roles'];
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function statePermissions()
    {
        return $this->belongsToMany(State::class, 'user_state_permissions');
    }

    public function lgaPermissions()
    {
        return $this->belongsToMany(Lga::class, 'user_lga_permissions');
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission($permission)
    {
        return $this->roles()->whereHas('permissions', function($q) use ($permission) {
            $q->where('name', $permission);
        })->exists();
    }

    public function canAccessState($stateId)
    {
        if ($this->hasRole('admin')) return true;
        
        return $this->statePermissions()->where('state_id', $stateId)->exists();
    }

    public function canAccessLga($lgaId)
    {
        if ($this->hasRole('admin')) return true;
        
        return $this->lgaPermissions()->where('lga_id', $lgaId)->exists();
    }

    public function wardPermissions()
    {
        return $this->belongsToMany(Ward::class, 'user_ward_permissions');
    }

    public function canAccessWard($wardId)
    {
        if ($this->hasRole('admin')) return true;
        
        return $this->wardPermissions()->where('ward_id', $wardId)->exists();
    }
}
