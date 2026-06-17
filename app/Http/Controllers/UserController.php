<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of users.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $authUser = auth()->user();

        $query = User::with(['roles', 'branch:id,name'])
            ->orderBy('name');

        if ($authUser->hasRole('gerente') && $authUser->branch_id) {
            $query->where('branch_id', $authUser->branch_id);
        }

        return Inertia::render('users/index', [
            'users' => $query->paginate(15, ['id', 'name', 'email', 'branch_id'])->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
                'branch' => $user->branch?->only('id', 'name'),
                'role' => $user->roles->first()?->name,
            ]),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('users/create', [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'roles' => Role::values(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = User::create($request->only('name', 'email', 'password', 'branch_id'));
        $user->syncRoles([$request->role]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuário criado com sucesso.']);

        return to_route('users.index');
    }

    /**
     * Show the form for editing the given user.
     */
    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('users/edit', [
            'user' => [
                ...$user->only('id', 'name', 'email', 'branch_id'),
                'role' => $user->roles->first()?->name,
            ],
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'roles' => Role::values(),
        ]);
    }

    /**
     * Update the given user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update($request->only('name', 'email', 'branch_id'));

        if ($request->filled('password')) {
            $user->update(['password' => $request->password]);
        }

        $user->syncRoles([$request->role]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuário atualizado com sucesso.']);

        return to_route('users.index');
    }

    /**
     * Remove the given user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuário removido com sucesso.']);

        return to_route('users.index');
    }
}
