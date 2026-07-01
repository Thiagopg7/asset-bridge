<?php

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role as SpatieRole;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates every role and permission', function () {
    expect(SpatieRole::count())->toBe(count(Role::cases()));

    foreach (Permission::values() as $permission) {
        expect(Spatie\Permission\Models\Permission::where('name', $permission)->exists())->toBeTrue();
    }
});

it('grants the gerente approval but not management permissions', function () {
    $user = User::factory()->gerente()->create();

    expect($user->can(Permission::RequestsApprove->value))->toBeTrue();
    expect($user->can(Permission::TransfersAuthorize->value))->toBeTrue();
    expect($user->can(Permission::BranchesManage->value))->toBeFalse();
    expect($user->can(Permission::RolesAssign->value))->toBeFalse();
});

it('limits the colaborador to requests, transfers and assets', function () {
    $user = User::factory()->colaborador()->create();

    expect($user->can(Permission::AssetsView->value))->toBeTrue();
    expect($user->can(Permission::RequestsCreate->value))->toBeTrue();
    expect($user->can(Permission::BranchesView->value))->toBeFalse();
    expect($user->can(Permission::RequestsApprove->value))->toBeFalse();
});

it('grants the admin every permission', function () {
    $user = User::factory()->admin()->create();

    foreach (Permission::values() as $permission) {
        expect($user->can($permission))->toBeTrue();
    }
});

it('reports a gerente as manager only of its own branch', function () {
    $own = Branch::factory()->create();
    $other = Branch::factory()->create();
    $user = User::factory()->gerente()->forBranch($own)->create();

    expect($user->isManagerOf($own))->toBeTrue();
    expect($user->isManagerOf($other))->toBeFalse();
});
