<?php

namespace App\Http\Controllers;

use App\Enums\ShipmentStatus;
use App\Enums\TransferStatus;
use App\Http\Requests\StoreTransferRequest;
use App\Models\AssetRequest;
use App\Models\Shipment;
use App\Models\Transfer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TransferController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the transfers the user's branch is involved in.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Transfer::class);

        $user = auth()->user();

        $transfers = Transfer::query()
            ->with([
                'assetRequest:id,branch_id,asset_id',
                'assetRequest.asset:id,name,unit',
                'assetRequest.branch:id,name',
                'branch:id,name',
                'user:id,name',
            ])
            ->unless($user->managesAllBranches(), fn ($query) => $query->where(function ($scoped) use ($user) {
                $scoped->where('branch_id', $user->branch_id)
                    ->orWhereHas('assetRequest', fn ($offer) => $offer->where('branch_id', $user->branch_id));
            }))
            ->latest()
            ->paginate(15)
            ->through(fn (Transfer $transfer) => [
                'id' => $transfer->id,
                'quantity' => $transfer->quantity,
                'status' => $transfer->status->value,
                'status_label' => $transfer->status->label(),
                'notes' => $transfer->notes,
                'asset_name' => $transfer->assetRequest->asset->name,
                'unit' => $transfer->assetRequest->asset->unit->value,
                'offer_branch_name' => $transfer->assetRequest->branch->name,
                'branch_name' => $transfer->branch->name,
                'user_name' => $transfer->user->name,
                'created_at' => $transfer->created_at,
                'can_review' => $user->can('review', $transfer),
                'can_delete' => $user->can('delete', $transfer),
            ]);

        return Inertia::render('transfers/index', [
            'transfers' => $transfers,
        ]);
    }

    /**
     * Store a newly requested transfer against the given offer.
     */
    public function store(StoreTransferRequest $request, AssetRequest $assetRequest): RedirectResponse
    {
        $this->authorize('create', Transfer::class);

        Transfer::create([
            ...$request->validated(),
            'asset_request_id' => $assetRequest->id,
            'user_id' => $request->user()->id,
            'branch_id' => $request->user()->branch_id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Solicitação de transferência enviada.']);

        return to_route('transfers.index');
    }

    /**
     * Authorize the given pending transfer, drawing down the offer's balance.
     */
    public function authorizeTransfer(Transfer $transfer): RedirectResponse
    {
        $this->authorize('review', $transfer);

        $authorized = DB::transaction(function () use ($transfer) {
            $offer = AssetRequest::query()
                ->whereKey($transfer->asset_request_id)
                ->lockForUpdate()
                ->first();

            if (($offer->available_quantity ?? 0) < $transfer->quantity) {
                return false;
            }

            $offer->decrement('available_quantity', $transfer->quantity);

            $transfer->update([
                'status' => TransferStatus::Authorized,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            Shipment::create([
                'transfer_id' => $transfer->id,
                'origin_branch_id' => $offer->branch_id,
                'destination_branch_id' => $transfer->branch_id,
                'status' => ShipmentStatus::Ready,
            ]);

            return true;
        });

        if (! $authorized) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Saldo da oferta insuficiente para autorizar.']);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Transferência autorizada.']);

        return to_route('transfers.index');
    }

    /**
     * Reject the given pending transfer.
     */
    public function reject(Transfer $transfer): RedirectResponse
    {
        $this->authorize('review', $transfer);

        $transfer->update([
            'status' => TransferStatus::Rejected,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Transferência rejeitada.']);

        return to_route('transfers.index');
    }

    /**
     * Remove (cancel) the given transfer request.
     */
    public function destroy(Transfer $transfer): RedirectResponse
    {
        $this->authorize('delete', $transfer);

        $transfer->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Solicitação de transferência cancelada.']);

        return to_route('transfers.index');
    }
}
