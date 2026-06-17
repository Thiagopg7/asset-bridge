<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::UsersView->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * Users with manage permission see everyone; otherwise, only same-branch users.
     */
    public function view(User $user, User $model): bool
    {
        if (! $user->can(Permission::UsersView->value)) {
            return false;
        }

        if ($user->can(Permission::UsersManage->value)) {
            return true;
        }

        return $user->branch_id !== null && $user->branch_id === $model->branch_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::UsersManage->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can(Permission::UsersManage->value);
    }

    /**
     * Determine whether the user can delete the model.
     * A user cannot delete themselves.
     */
    public function delete(User $user, User $model): bool
    {
        if (! $user->can(Permission::UsersManage->value)) {
            return false;
        }

        return $user->id !== $model->id;
    }
}
