<?php

use App\Models\Asset;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->asset = Asset::factory()->create();
});

it('allows any authenticated role to view assets', function () {
    $branch = Branch::factory()->create();

    foreach (['admin', 'diretor', 'gerente', 'colaborador'] as $role) {
        $user = User::factory()->{$role}()->forBranch($branch)->create();

        expect($user->can('viewAny', Asset::class))->toBeTrue()
            ->and($user->can('view', $this->asset))->toBeTrue();
    }
});

it('allows admin and diretor to manage assets', function () {
    $admin = User::factory()->admin()->create();
    $diretor = User::factory()->diretor()->create();

    foreach ([$admin, $diretor] as $user) {
        expect($user->can('create', Asset::class))->toBeTrue()
            ->and($user->can('update', $this->asset))->toBeTrue()
            ->and($user->can('delete', $this->asset))->toBeTrue();
    }
});

it('forbids gerente and colaborador from managing assets', function () {
    $branch = Branch::factory()->create();
    $gerente = User::factory()->gerente()->forBranch($branch)->create();
    $colaborador = User::factory()->colaborador()->forBranch($branch)->create();

    foreach ([$gerente, $colaborador] as $user) {
        expect($user->can('create', Asset::class))->toBeFalse()
            ->and($user->can('update', $this->asset))->toBeFalse()
            ->and($user->can('delete', $this->asset))->toBeFalse();
    }
});
