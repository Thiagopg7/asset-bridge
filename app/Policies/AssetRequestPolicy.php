<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\AssetRequest;
use App\Models\User;

class AssetRequestPolicy
{
    /**
     * Determine whether the user can view any requests.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::RequestsView->value);
    }

    /**
     * Determine whether the user can view the request.
     *
     * Admin/Diretor see any branch; everyone else sees only their own branch.
     */
    public function view(User $user, AssetRequest $assetRequest): bool
    {
        if (! $user->can(Permission::RequestsView->value)) {
            return false;
        }

        return $user->managesAllBranches()
            || $user->branch_id === $assetRequest->branch_id;
    }

    /**
     * Determine whether the user can create a request.
     *
     * The user must be assigned to a branch to open a request for it.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::RequestsCreate->value)
            && $user->branch_id !== null;
    }

    /**
     * Determine whether the user can review (approve/reject) the request.
     *
     * Only pending requests can be reviewed. Admin/Diretor review any branch;
     * Gerente reviews only their own branch.
     */
    public function review(User $user, AssetRequest $assetRequest): bool
    {
        if (! $assetRequest->isPending() || ! $user->can(Permission::RequestsApprove->value)) {
            return false;
        }

        return $user->managesAllBranches()
            || $user->isManagerOf($assetRequest->branch);
    }

    /**
     * Determine whether the user can delete (cancel) the request.
     *
     * The author may cancel their own pending request; admin may cancel any.
     */
    public function delete(User $user, AssetRequest $assetRequest): bool
    {
        if ($user->managesAllBranches()) {
            return true;
        }

        return $assetRequest->isPending()
            && $assetRequest->user_id === $user->id;
    }
}
