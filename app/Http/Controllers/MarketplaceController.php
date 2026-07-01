<?php

namespace App\Http\Controllers;

use App\Enums\AssetRequestStatus;
use App\Enums\AssetRequestType;
use App\Models\AssetRequest;
use App\Models\Transfer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the available surplus offers from other branches.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Transfer::class);

        $user = auth()->user();

        $offers = AssetRequest::query()
            ->where('type', AssetRequestType::Surplus)
            ->where('status', AssetRequestStatus::Approved)
            ->where('available_quantity', '>', 0)
            ->when($user->branch_id, fn ($query) => $query->where('branch_id', '!=', $user->branch_id))
            ->with(['asset:id,name,unit', 'branch:id,name'])
            ->latest()
            ->paginate(15)
            ->through(fn (AssetRequest $offer) => [
                'id' => $offer->id,
                'asset_name' => $offer->asset->name,
                'unit' => $offer->asset->unit->value,
                'branch_name' => $offer->branch->name,
                'quantity' => $offer->quantity,
                'available_quantity' => $offer->available_quantity,
                'notes' => $offer->notes,
                'created_at' => $offer->created_at,
            ]);

        return Inertia::render('marketplace/index', [
            'offers' => $offers,
            'canRequest' => $user->can('create', Transfer::class),
        ]);
    }
}
