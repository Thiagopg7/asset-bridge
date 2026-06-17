<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->branch = Branch::factory()->create();
    $this->otherBranch = Branch::factory()->create();
});

it('lets an admin view and manage every user', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->colaborador()->forBranch($this->branch)->create();

    expect($admin->can('viewAny', User::class))->toBeTrue();
    expect($admin->can('view', $target))->toBeTrue();
    expect($admin->can('create', User::class))->toBeTrue();
    expect($admin->can('update', $target))->toBeTrue();
    expect($admin->can('delete', $target))->toBeTrue();
});

it('prevents an admin from deleting themselves', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('delete', $admin))->toBeFalse();
});

it('lets a gerente view only users from their branch', function () {
    $gerente = User::factory()->gerente()->forBranch($this->branch)->create();
    $sameBranchUser = User::factory()->colaborador()->forBranch($this->branch)->create();
    $otherBranchUser = User::factory()->colaborador()->forBranch($this->otherBranch)->create();

    expect($gerente->can('viewAny', User::class))->toBeTrue();
    expect($gerente->can('view', $sameBranchUser))->toBeTrue();
    expect($gerente->can('view', $otherBranchUser))->toBeFalse();
    expect($gerente->can('create', User::class))->toBeFalse();
    expect($gerente->can('update', $sameBranchUser))->toBeFalse();
    expect($gerente->can('delete', $sameBranchUser))->toBeFalse();
});

it('denies a colaborador from any user management', function () {
    $colaborador = User::factory()->colaborador()->forBranch($this->branch)->create();
    $target = User::factory()->colaborador()->forBranch($this->branch)->create();

    expect($colaborador->can('viewAny', User::class))->toBeFalse();
    expect($colaborador->can('view', $target))->toBeFalse();
    expect($colaborador->can('create', User::class))->toBeFalse();
    expect($colaborador->can('update', $target))->toBeFalse();
    expect($colaborador->can('delete', $target))->toBeFalse();
});
