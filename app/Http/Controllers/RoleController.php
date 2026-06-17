<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the permission matrix for all roles.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $roles = Role::with('permissions:name')->get(['id', 'name']);

        return Inertia::render('roles/index', [
            'roles' => $roles->map(fn (Role $role) => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ]),
            'permissions' => Permission::values(),
        ]);
    }
}
