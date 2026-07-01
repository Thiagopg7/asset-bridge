<?php

use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Branch;
use App\Models\Transfer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->offerBranch = Branch::factory()->create();
    $this->destinationBranch = Branch::factory()->create();
    $this->asset = Asset::factory()->create();
    $this->offer = AssetRequest::factory()
        ->surplus()
        ->approved()
        ->for($this->offerBranch)
        ->for($this->asset)
        ->create(['quantity' => 20, 'available_quantity' => 20]);
});

it('lets a collaborator with a branch request a transfer', function () {
    $withBranch = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();
    $withoutBranch = User::factory()->colaborador()->create();

    expect($withBranch->can('create', Transfer::class))->toBeTrue()
        ->and($withoutBranch->can('create', Transfer::class))->toBeFalse();
});

it('lets the requesting branch and the offering branch view a transfer', function () {
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->create();

    $destinationUser = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();
    $offerUser = User::factory()->colaborador()->forBranch($this->offerBranch)->create();
    $strangerBranch = Branch::factory()->create();
    $stranger = User::factory()->colaborador()->forBranch($strangerBranch)->create();

    expect($destinationUser->can('view', $transfer))->toBeTrue()
        ->and($offerUser->can('view', $transfer))->toBeTrue()
        ->and($stranger->can('view', $transfer))->toBeFalse();
});

it('only allows the destination branch manager to review pending transfers', function () {
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->create();

    $destinationManager = User::factory()->gerente()->forBranch($this->destinationBranch)->create();
    $offerManager = User::factory()->gerente()->forBranch($this->offerBranch)->create();
    $collaborator = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();

    expect($destinationManager->can('review', $transfer))->toBeTrue()
        ->and($offerManager->can('review', $transfer))->toBeFalse()
        ->and($collaborator->can('review', $transfer))->toBeFalse();
});

it('cannot review a transfer that is no longer pending', function () {
    $authorized = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->authorized()->create();
    $manager = User::factory()->gerente()->forBranch($this->destinationBranch)->create();

    expect($manager->can('review', $authorized))->toBeFalse();
});

it('lets an admin delete any transfer but a collaborator only their own pending one', function () {
    $author = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();
    $other = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();
    $admin = User::factory()->admin()->create();
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->for($author)->create();

    expect($admin->can('delete', $transfer))->toBeTrue()
        ->and($author->can('delete', $transfer))->toBeTrue()
        ->and($other->can('delete', $transfer))->toBeFalse();
});
