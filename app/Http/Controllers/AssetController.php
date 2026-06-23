<?php

namespace App\Http\Controllers;

use App\Enums\AssetUnit;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of assets.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Asset::class);

        return Inertia::render('assets/index', [
            'assets' => Asset::query()
                ->orderBy('name')
                ->paginate(15, ['id', 'name', 'description', 'unit', 'active']),
            'units' => AssetUnit::values(),
        ]);
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create(): Response
    {
        $this->authorize('create', Asset::class);

        return Inertia::render('assets/create', [
            'units' => AssetUnit::values(),
        ]);
    }

    /**
     * Store a newly created asset.
     */
    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $this->authorize('create', Asset::class);

        Asset::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ativo criado com sucesso.']);

        return to_route('assets.index');
    }

    /**
     * Show the form for editing the given asset.
     */
    public function edit(Asset $asset): Response
    {
        $this->authorize('update', $asset);

        return Inertia::render('assets/edit', [
            'asset' => $asset->only('id', 'name', 'description', 'unit', 'active'),
            'units' => AssetUnit::values(),
        ]);
    }

    /**
     * Update the given asset.
     */
    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $this->authorize('update', $asset);

        $asset->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ativo atualizado com sucesso.']);

        return to_route('assets.index');
    }

    /**
     * Remove the given asset.
     */
    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ativo removido com sucesso.']);

        return to_route('assets.index');
    }
}
