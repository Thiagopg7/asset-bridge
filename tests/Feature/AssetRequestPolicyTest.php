<?php

use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->branch = Branch::factory()->create();
    $this->otherBranch = Branch::factory()->create();
    $this->asset = Asset::factory()->create();
});

it('lets a director view requests from any branch', function () {
    $diretor = User::factory()->diretor()->create();
    $request = AssetRequest::factory()->for($this->otherBranch)->for($this->asset)->create();

    expect($diretor->can('view', $request))->toBeTrue();
});

it('prevents a collaborator from viewing requests of another branch', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->otherBranch)->for($this->asset)->create();

    expect($user->can('view', $request))->toBeFalse();
});

it('prevents creating a request without a branch', function () {
    $withBranch = User::factory()->colaborador()->forBranch($this->branch)->create();
    $withoutBranch = User::factory()->colaborador()->create();

    expect($withBranch->can('create', AssetRequest::class))->toBeTrue()
        ->and($withoutBranch->can('create', AssetRequest::class))->toBeFalse();
});

it('only allows reviewing pending requests', function () {
    $gerente = User::factory()->gerente()->forBranch($this->branch)->create();
    $pending = AssetRequest::factory()->for($this->branch)->for($this->asset)->create();
    $approved = AssetRequest::factory()->for($this->branch)->for($this->asset)->approved()->create();

    expect($gerente->can('review', $pending))->toBeTrue()
        ->and($gerente->can('review', $approved))->toBeFalse();
});

it('lets an admin delete any request but a collaborator only their own pending one', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->colaborador()->forBranch($this->branch)->create();
    $other = User::factory()->colaborador()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->branch)->for($this->asset)->for($author)->create();

    expect($admin->can('delete', $request))->toBeTrue()
        ->and($author->can('delete', $request))->toBeTrue()
        ->and($other->can('delete', $request))->toBeFalse();
});
