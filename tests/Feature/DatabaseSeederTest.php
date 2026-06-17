<?php

use App\Enums\Role;
use App\Models\Branch;
use App\Models\User;

it('seeds branches, users and roles with the expected structure', function () {
    $this->seed();

    expect(Branch::count())->toBe(3);

    // 1 admin global + por filial (1 gerente + 3 colaboradores) = 1 + 3 * 4
    expect(User::count())->toBe(13);

    expect(User::role(Role::Admin->value)->count())->toBe(1);
    expect(User::role(Role::Gerente->value)->count())->toBe(3);
    expect(User::role(Role::Colaborador->value)->count())->toBe(9);

    $admin = User::where('email', 'admin@asset-bridge.test')->first();
    expect($admin->branch_id)->toBeNull();

    Branch::all()->each(function (Branch $branch) {
        expect($branch->users()->count())->toBe(4);
    });
});

it('is idempotent when run twice', function () {
    $this->seed();
    $this->seed();

    expect(Branch::count())->toBe(3);
    expect(User::count())->toBe(13);
});
