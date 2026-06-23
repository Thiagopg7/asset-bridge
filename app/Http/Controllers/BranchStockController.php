<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStockQuantityRequest;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\StockItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BranchStockController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the stock for the given branch.
     */
    public function index(Branch $branch): Response
    {
        $this->authorize('stock.view', $branch);

        $canUpdate = auth()->user()->can('stock.update', $branch);

        $stockByAsset = StockItem::where('branch_id', $branch->id)
            ->pluck('quantity', 'asset_id');

        $stock = Asset::query()
            ->orderBy('name')
            ->get(['id', 'name', 'unit'])
            ->map(fn (Asset $asset) => [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'unit' => $asset->unit->value,
                'quantity' => $stockByAsset->get($asset->id, 0),
            ]);

        return Inertia::render('branches/stock', [
            'branch' => $branch->only('id', 'name', 'code'),
            'stock' => $stock,
            'canUpdate' => $canUpdate,
        ]);
    }

    /**
     * Update the stock quantity for a specific asset in the given branch.
     */
    public function update(UpdateStockQuantityRequest $request, Branch $branch, Asset $asset): RedirectResponse
    {
        $this->authorize('stock.update', $branch);

        StockItem::updateOrCreate(
            ['asset_id' => $asset->id, 'branch_id' => $branch->id],
            ['quantity' => $request->validated('quantity')],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Quantidade atualizada com sucesso.']);

        return to_route('branches.stock.index', $branch);
    }
}
