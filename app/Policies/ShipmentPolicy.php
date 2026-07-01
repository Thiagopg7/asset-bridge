<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Determine whether the user can view any shipments.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::DispatchView->value);
    }

    /**
     * Determine whether the user can view the shipment.
     *
     * Admin/Diretor see any branch; the origin and destination branches see
     * the shipments they take part in.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if (! $user->can(Permission::DispatchView->value)) {
            return false;
        }

        return $user->managesAllBranches()
            || $user->branch_id === $shipment->origin_branch_id
            || $user->branch_id === $shipment->destination_branch_id;
    }

    /**
     * Determine whether the user can dispatch (send) the shipment.
     *
     * Only ready shipments can be dispatched by the origin branch logistics.
     */
    public function dispatch(User $user, Shipment $shipment): bool
    {
        if (! $shipment->isReady() || ! $user->can(Permission::DispatchExecute->value)) {
            return false;
        }

        return $user->managesAllBranches()
            || $user->branch_id === $shipment->origin_branch_id;
    }

    /**
     * Determine whether the user can confirm receipt of the shipment.
     *
     * Only in-transit shipments can be received by the destination branch.
     */
    public function receive(User $user, Shipment $shipment): bool
    {
        if (! $shipment->isInTransit() || ! $user->can(Permission::DispatchReceive->value)) {
            return false;
        }

        return $user->managesAllBranches()
            || $user->branch_id === $shipment->destination_branch_id;
    }
}
