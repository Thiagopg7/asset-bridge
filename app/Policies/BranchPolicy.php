<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::BranchesView->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * Users that can manage branches (admin/diretor) see every branch;
     * everyone else only sees the branch they are assigned to.
     */
    public function view(User $user, Branch $branch): bool
    {
        if (! $user->can(Permission::BranchesView->value)) {
            return false;
        }

        return $user->can(Permission::BranchesManage->value)
            || $user->belongsToBranch($branch);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::BranchesManage->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Branch $branch): bool
    {
        return $user->can(Permission::BranchesManage->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return $user->can(Permission::BranchesManage->value);
    }
}
