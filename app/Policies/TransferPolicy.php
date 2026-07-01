<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Transfer;
use App\Models\User;

class TransferPolicy
{
    /**
     * Determine whether the user can view any transfers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::TransfersView->value);
    }

    /**
     * Determine whether the user can view the transfer.
     *
     * Admin/Diretor see any branch; the requesting branch and the offering
     * branch both see transfers they are involved in.
     */
    public function view(User $user, Transfer $transfer): bool
    {
        if (! $user->can(Permission::TransfersView->value)) {
            return false;
        }

        return $user->managesAllBranches()
            || $user->branch_id === $transfer->branch_id
            || $user->branch_id === $transfer->assetRequest->branch_id;
    }

    /**
     * Determine whether the user can request a transfer.
     *
     * The user must be assigned to a branch to request one for it. Offer-specific
     * rules (availability, different branch, quantity) live in the form request.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::TransfersCreate->value)
            && $user->branch_id !== null;
    }

    /**
     * Determine whether the user can review (authorize/reject) the transfer.
     *
     * Only pending transfers can be reviewed by the requesting branch's manager.
     */
    public function review(User $user, Transfer $transfer): bool
    {
        if (! $transfer->isPending() || ! $user->can(Permission::TransfersAuthorize->value)) {
            return false;
        }

        return $user->managesAllBranches()
            || $user->isManagerOf($transfer->branch);
    }

    /**
     * Determine whether the user can delete (cancel) the transfer.
     *
     * The author may cancel their own pending request; admin may cancel any.
     */
    public function delete(User $user, Transfer $transfer): bool
    {
        if ($user->managesAllBranches()) {
            return true;
        }

        return $transfer->isPending()
            && $transfer->user_id === $user->id;
    }
}
