<?php

namespace App\Http\Controllers;

use App\Enums\AssetRequestStatus;
use App\Enums\AssetRequestType;
use App\Http\Requests\StoreAssetRequestRequest;
use App\Models\Asset;
use App\Models\AssetRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AssetRequestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of asset requests scoped to the user's visibility.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', AssetRequest::class);

        $user = auth()->user();

        $requests = AssetRequest::query()
            ->with(['asset:id,name,unit', 'branch:id,name', 'user:id,name'])
            ->unless($user->managesAllBranches(), fn ($query) => $query->where('branch_id', $user->branch_id))
            ->latest()
            ->paginate(15)
            ->through(fn (AssetRequest $request) => [
                'id' => $request->id,
                'type' => $request->type->value,
                'type_label' => $request->type->label(),
                'status' => $request->status->value,
                'status_label' => $request->status->label(),
                'quantity' => $request->quantity,
                'notes' => $request->notes,
                'asset_name' => $request->asset->name,
                'unit' => $request->asset->unit->value,
                'branch_name' => $request->branch->name,
                'user_name' => $request->user->name,
                'created_at' => $request->created_at,
                'can_review' => $user->can('review', $request),
                'can_edit' => $user->can('update', $request),
                'can_delete' => $user->can('delete', $request),
            ]);

        return Inertia::render('asset-requests/index', [
            'requests' => $requests,
            'canCreate' => $user->can('create', AssetRequest::class),
        ]);
    }

    /**
     * Show the form for creating a new asset request.
     */
    public function create(): Response
    {
        $this->authorize('create', AssetRequest::class);

        return Inertia::render('asset-requests/create', [
            'assets' => Asset::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'unit']),
            'types' => collect(AssetRequestType::cases())
                ->map(fn (AssetRequestType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ]),
        ]);
    }

    /**
     * Store a newly created asset request.
     */
    public function store(StoreAssetRequestRequest $request): RedirectResponse
    {
        $this->authorize('create', AssetRequest::class);

        AssetRequest::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'branch_id' => $request->user()->branch_id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Solicitação criada com sucesso.']);

        return to_route('asset-requests.index');
    }

    /**
     * Display the details of a single request.
     */
    public function show(AssetRequest $assetRequest): Response
    {
        $this->authorize('view', $assetRequest);

        $user = auth()->user();

        $assetRequest->load(['asset:id,name,unit', 'branch:id,name', 'user:id,name', 'reviewer:id,name']);

        return Inertia::render('asset-requests/show', [
            'request' => [
                'id' => $assetRequest->id,
                'type' => $assetRequest->type->value,
                'type_label' => $assetRequest->type->label(),
                'status' => $assetRequest->status->value,
                'status_label' => $assetRequest->status->label(),
                'quantity' => $assetRequest->quantity,
                'notes' => $assetRequest->notes,
                'asset_name' => $assetRequest->asset->name,
                'unit' => $assetRequest->asset->unit->value,
                'branch_name' => $assetRequest->branch->name,
                'user_name' => $assetRequest->user->name,
                'reviewer_name' => $assetRequest->reviewer?->name,
                'reviewed_at' => $assetRequest->reviewed_at,
                'created_at' => $assetRequest->created_at,
                'can_review' => $user->can('review', $assetRequest),
                'can_edit' => $user->can('update', $assetRequest),
                'can_delete' => $user->can('delete', $assetRequest),
            ],
        ]);
    }

    /**
     * Show the form for editing a pending request.
     */
    public function edit(AssetRequest $assetRequest): Response
    {
        $this->authorize('update', $assetRequest);

        return Inertia::render('asset-requests/edit', [
            'assetRequest' => [
                'id' => $assetRequest->id,
                'asset_id' => $assetRequest->asset_id,
                'type' => $assetRequest->type->value,
                'quantity' => $assetRequest->quantity,
                'notes' => $assetRequest->notes,
            ],
            'assets' => Asset::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'unit']),
            'types' => collect(AssetRequestType::cases())
                ->map(fn (AssetRequestType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ]),
        ]);
    }

    /**
     * Update the given pending request.
     */
    public function update(StoreAssetRequestRequest $request, AssetRequest $assetRequest): RedirectResponse
    {
        $this->authorize('update', $assetRequest);

        $assetRequest->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Solicitação atualizada com sucesso.']);

        return to_route('asset-requests.index');
    }

    /**
     * Approve the given pending request.
     *
     * Approving a surplus offer opens its full quantity for transfer.
     */
    public function approve(AssetRequest $assetRequest): RedirectResponse
    {
        $this->authorize('review', $assetRequest);

        $assetRequest->update([
            'status' => AssetRequestStatus::Approved,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'available_quantity' => $assetRequest->type === AssetRequestType::Surplus
                ? $assetRequest->quantity
                : null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Solicitação aprovada.']);

        return to_route('asset-requests.index');
    }

    /**
     * Reject the given pending request.
     */
    public function reject(AssetRequest $assetRequest): RedirectResponse
    {
        $this->authorize('review', $assetRequest);

        $assetRequest->update([
            'status' => AssetRequestStatus::Rejected,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Solicitação rejeitada.']);

        return to_route('asset-requests.index');
    }

    /**
     * Remove (cancel) the given request.
     */
    public function destroy(AssetRequest $assetRequest): RedirectResponse
    {
        $this->authorize('delete', $assetRequest);

        $assetRequest->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Solicitação removida.']);

        return to_route('asset-requests.index');
    }
}
