<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->branch = Branch::factory()->create();
    $this->otherBranch = Branch::factory()->create();
});

it('lets a colaborador view only its own branch', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();

    expect($user->can('view', $this->branch))->toBeTrue();
    expect($user->can('view', $this->otherBranch))->toBeFalse();
    expect($user->can('viewAny', Branch::class))->toBeTrue();
});

it('lets an admin view and manage every branch', function () {
    $user = User::factory()->admin()->create();

    expect($user->can('view', $this->branch))->toBeTrue();
    expect($user->can('view', $this->otherBranch))->toBeTrue();
    expect($user->can('create', Branch::class))->toBeTrue();
    expect($user->can('update', $this->branch))->toBeTrue();
    expect($user->can('delete', $this->branch))->toBeTrue();
});

it('forbids a gerente from managing branches', function () {
    $user = User::factory()->gerente()->forBranch($this->branch)->create();

    expect($user->can('view', $this->branch))->toBeTrue();
    expect($user->can('create', Branch::class))->toBeFalse();
    expect($user->can('update', $this->branch))->toBeFalse();
});
