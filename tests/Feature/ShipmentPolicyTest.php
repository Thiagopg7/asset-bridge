<?php

use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\Transfer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->originBranch = Branch::factory()->create();
    $this->destinationBranch = Branch::factory()->create();
    $this->asset = Asset::factory()->create();
    $this->offer = AssetRequest::factory()->surplus()->approved()
        ->for($this->originBranch)->for($this->asset)
        ->create(['quantity' => 20, 'available_quantity' => 20]);
    $this->transfer = Transfer::factory()->authorized()
        ->for($this->offer)->for($this->destinationBranch)
        ->create(['quantity' => 5]);
});

it('requires the dispatch view permission to list shipments', function () {
    $logistica = User::factory()->logistica()->forBranch($this->originBranch)->create();
    $colaborador = User::factory()->colaborador()->forBranch($this->originBranch)->create();

    expect($logistica->can('viewAny', Shipment::class))->toBeTrue()
        ->and($colaborador->can('viewAny', Shipment::class))->toBeFalse();
});

it('lets the origin and destination branches view a shipment', function () {
    $shipment = Shipment::factory()->forTransfer($this->transfer)->create();

    $origin = User::factory()->logistica()->forBranch($this->originBranch)->create();
    $destination = User::factory()->logistica()->forBranch($this->destinationBranch)->create();
    $strangerBranch = Branch::factory()->create();
    $stranger = User::factory()->logistica()->forBranch($strangerBranch)->create();

    expect($origin->can('view', $shipment))->toBeTrue()
        ->and($destination->can('view', $shipment))->toBeTrue()
        ->and($stranger->can('view', $shipment))->toBeFalse();
});

it('only lets the origin logistics dispatch a ready shipment', function () {
    $shipment = Shipment::factory()->forTransfer($this->transfer)->create();

    $origin = User::factory()->logistica()->forBranch($this->originBranch)->create();
    $destination = User::factory()->logistica()->forBranch($this->destinationBranch)->create();

    expect($origin->can('dispatch', $shipment))->toBeTrue()
        ->and($destination->can('dispatch', $shipment))->toBeFalse();
});

it('cannot dispatch a shipment that already left', function () {
    $shipment = Shipment::factory()->forTransfer($this->transfer)->inTransit()->create();
    $origin = User::factory()->logistica()->forBranch($this->originBranch)->create();

    expect($origin->can('dispatch', $shipment))->toBeFalse();
});

it('only lets the destination logistics receive an in-transit shipment', function () {
    $shipment = Shipment::factory()->forTransfer($this->transfer)->inTransit()->create();

    $origin = User::factory()->logistica()->forBranch($this->originBranch)->create();
    $destination = User::factory()->logistica()->forBranch($this->destinationBranch)->create();

    expect($destination->can('receive', $shipment))->toBeTrue()
        ->and($origin->can('receive', $shipment))->toBeFalse();
});

it('cannot receive a shipment that has not been dispatched', function () {
    $shipment = Shipment::factory()->forTransfer($this->transfer)->create();
    $destination = User::factory()->logistica()->forBranch($this->destinationBranch)->create();

    expect($destination->can('receive', $shipment))->toBeFalse();
});
