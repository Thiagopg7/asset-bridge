<?php

namespace App\Http\Controllers;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use App\Models\StockItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ShipmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the dispatch queue scoped to the user's branch.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Shipment::class);

        $user = auth()->user();

        $shipments = Shipment::query()
            ->with([
                'transfer:id,asset_request_id,branch_id,quantity',
                'transfer.assetRequest:id,asset_id',
                'transfer.assetRequest.asset:id,name,unit',
                'originBranch:id,name',
                'destinationBranch:id,name',
            ])
            ->unless($user->managesAllBranches(), fn ($query) => $query->where(function ($scoped) use ($user) {
                $scoped->where('origin_branch_id', $user->branch_id)
                    ->orWhere('destination_branch_id', $user->branch_id);
            }))
            ->latest()
            ->paginate(15)
            ->through(fn (Shipment $shipment) => [
                'id' => $shipment->id,
                'asset_name' => $shipment->transfer->assetRequest->asset->name,
                'unit' => $shipment->transfer->assetRequest->asset->unit->value,
                'quantity' => $shipment->transfer->quantity,
                'origin_branch_name' => $shipment->originBranch->name,
                'destination_branch_name' => $shipment->destinationBranch->name,
                'status' => $shipment->status->value,
                'status_label' => $shipment->status->label(),
                'created_at' => $shipment->created_at,
                'can_dispatch' => $user->can('dispatch', $shipment),
                'can_receive' => $user->can('receive', $shipment),
            ]);

        return Inertia::render('shipments/index', [
            'shipments' => $shipments,
        ]);
    }

    /**
     * Mark the given shipment as dispatched (in transit).
     */
    public function dispatch(Shipment $shipment): RedirectResponse
    {
        $this->authorize('dispatch', $shipment);

        $shipment->update([
            'status' => ShipmentStatus::InTransit,
            'dispatched_by' => auth()->id(),
            'dispatched_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Expedição enviada.']);

        return to_route('shipments.index');
    }

    /**
     * Confirm receipt of the shipment, moving the stock between branches.
     */
    public function receive(Shipment $shipment): RedirectResponse
    {
        $this->authorize('receive', $shipment);

        $received = DB::transaction(function () use ($shipment) {
            $locked = Shipment::query()
                ->whereKey($shipment->id)
                ->lockForUpdate()
                ->first();

            // Guard against a concurrent receipt confirming the same shipment twice.
            if (! $locked->isInTransit()) {
                return false;
            }

            $locked->loadMissing('transfer.assetRequest');
            $assetId = $locked->transfer->assetRequest->asset_id;
            $quantity = $locked->transfer->quantity;

            $originStock = StockItem::query()
                ->where('branch_id', $locked->origin_branch_id)
                ->where('asset_id', $assetId)
                ->lockForUpdate()
                ->first();

            if ($originStock !== null) {
                $originStock->decrement('quantity', min($quantity, $originStock->quantity));
            }

            $destinationStock = StockItem::query()
                ->where('branch_id', $locked->destination_branch_id)
                ->where('asset_id', $assetId)
                ->lockForUpdate()
                ->first();

            if ($destinationStock !== null) {
                $destinationStock->increment('quantity', $quantity);
            } else {
                StockItem::create([
                    'branch_id' => $locked->destination_branch_id,
                    'asset_id' => $assetId,
                    'quantity' => $quantity,
                ]);
            }

            $locked->update([
                'status' => ShipmentStatus::Received,
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);

            return true;
        });

        if (! $received) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Esta expedição já foi recebida.']);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Recebimento confirmado. Estoque atualizado.']);

        return to_route('shipments.index');
    }
}
