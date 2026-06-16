<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the branches.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Branch::class);

        return Inertia::render('branches/index', [
            'branches' => Branch::query()
                ->orderBy('name')
                ->paginate(15, ['id', 'name', 'code', 'city', 'state', 'active']),
        ]);
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create(): Response
    {
        $this->authorize('create', Branch::class);

        return Inertia::render('branches/create');
    }

    /**
     * Store a newly created branch.
     */
    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        Branch::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Filial criada com sucesso.']);

        return to_route('branches.index');
    }

    /**
     * Show the form for editing the given branch.
     */
    public function edit(Branch $branch): Response
    {
        $this->authorize('update', $branch);

        return Inertia::render('branches/edit', [
            'branch' => $branch->only('id', 'name', 'code', 'city', 'state', 'active'),
        ]);
    }

    /**
     * Update the given branch.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->authorize('update', $branch);

        $branch->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Filial atualizada com sucesso.']);

        return to_route('branches.index');
    }

    /**
     * Remove the given branch.
     */
    public function destroy(Branch $branch): RedirectResponse
    {
        $this->authorize('delete', $branch);

        $branch->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Filial removida com sucesso.']);

        return to_route('branches.index');
    }
}
