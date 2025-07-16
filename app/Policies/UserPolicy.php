<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermission('manage_users');
    }

    public function view(User $user, User $model)
    {
        return $user->hasPermission('manage_users');
    }

    public function create(User $user)
    {
        return $user->hasPermission('manage_users');
    }

    public function update(User $user, User $model)
    {
        return $user->hasPermission('manage_users');
    }

    public function delete(User $user, User $model)
    {
        return $user->hasPermission('manage_users');
    }
}