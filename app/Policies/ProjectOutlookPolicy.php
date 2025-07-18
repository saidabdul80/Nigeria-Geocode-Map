<?php
// app/Policies/ProjectOutlookPolicy.php
namespace App\Policies;

use App\Models\ProjectOutlook;
use App\Models\User;

class ProjectOutlookPolicy
{
    public function view(User $user)
    {
        return $user->hasPermission('view_project_outlooks');
    }

    public function create(User $user)
    {
        return $user->hasPermission('create_project_outlooks');
    }

    public function update(User $user, ProjectOutlook $projectOutlook)
    {
        return $user->hasPermission('edit_project_outlooks') && 
               ($user->canAccessState($projectOutlook->state_id) || 
                $user->canAccessLga($projectOutlook->lga_id));
    }

    public function delete(User $user, ProjectOutlook $projectOutlook)
    {
        return $user->hasPermission('delete_project_outlooks') && 
               ($user->canAccessState($projectOutlook->state_id) || 
                $user->canAccessLga($projectOutlook->lga_id));
    }
}