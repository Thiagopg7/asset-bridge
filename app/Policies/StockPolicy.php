<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Branch;
use App\Models\User;

class StockPolicy
{
    /**
     * Determine whether the user can view the stock of a branch.
     *
     * Admin/Diretor see any branch; Gerente and Colaborador see only their own.
     */
    public function viewAny(User $user, Branch $branch): bool
    {
        if (! $user->can(Permission::AssetsView->value)) {
            return false;
        }

        return $user->can(Permission::AssetsManage->value)
            || $user->belongsToBranch($branch);
    }

    /**
     * Determine whether the user can adjust stock quantities for a branch.
     *
     * Admin/Diretor can adjust any branch; Gerente can adjust their own branch.
     */
    public function update(User $user, Branch $branch): bool
    {
        if ($user->can(Permission::AssetsManage->value)) {
            return true;
        }

        return $user->isManagerOf($branch);
    }
}
