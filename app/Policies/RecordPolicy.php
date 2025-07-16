<?php

namespace App\Policies;

use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecordPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermission('view_records');
    }

    public function view(User $user, Record $record)
    {
        return $user->hasPermission('view_records') && 
               ($user->canAccessState($record->state_id) || 
               $user->canAccessLga($record->lga_id));
    }

    public function create(User $user)
    {
        return $user->hasPermission('create_records');
    }

    public function update(User $user, Record $record)
    {
        return $user->hasPermission('edit_records') && 
               ($user->canAccessState($record->state_id) || 
               $user->canAccessLga($record->lga_id));
    }

    public function delete(User $user, Record $record)
    {
        return $user->hasPermission('delete_records') && 
               ($user->canAccessState($record->state_id) || 
               $user->canAccessLga($record->lga_id));
    }
}
