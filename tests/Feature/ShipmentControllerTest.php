<?php

use App\Enums\ShipmentStatus;
use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\StockItem;
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

// index
it('lists shipments involving the user branch only', function () {
    $logistica = User::factory()->logistica()->forBranch($this->originBranch)->create();
    $mine = Shipment::factory()->forTransfer($this->transfer)->create();
    Shipment::factory()->create();

    $this->actingAs($logistica)
        ->get('/shipments')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shipments/index')
            ->has('shipments.data', 1)
            ->where('shipments.data.0.id', $mine->id)
        );
});

it('denies a collaborator without dispatch permission from the queue', function () {
    $colaborador = User::factory()->colaborador()->forBranch($this->originBranch)->create();

    $this->actingAs($colaborador)
        ->get('/shipments')
        ->assertForbidden();
});

// dispatch
it('lets the origin logistics dispatch a ready shipment', function () {
    $logistica = User::factory()->logistica()->forBranch($this->originBranch)->create();
    $shipment = Shipment::factory()->forTransfer($this->transfer)->create();

    $this->actingAs($logistica)
        ->patch("/shipments/{$shipment->id}/dispatch")
        ->assertRedirect('/shipments');

    expect($shipment->fresh()->status)->toBe(ShipmentStatus::InTransit);
});

it('denies the destination branch from dispatching', function () {
    $logistica = User::factory()->logistica()->forBranch($this->destinationBranch)->create();
    $shipment = Shipment::factory()->forTransfer($this->transfer)->create();

    $this->actingAs($logistica)
        ->patch("/shipments/{$shipment->id}/dispatch")
        ->assertForbidden();
});

// receive (stock movement)
it('moves stock between branches when the destination confirms receipt', function () {
    $logistica = User::factory()->logistica()->forBranch($this->destinationBranch)->create();
    $shipment = Shipment::factory()->forTransfer($this->transfer)->inTransit()->create();

    StockItem::factory()->for($this->originBranch)->for($this->asset)->create(['quantity' => 12]);
    StockItem::factory()->for($this->destinationBranch)->for($this->asset)->create(['quantity' => 3]);

    $this->actingAs($logistica)
        ->patch("/shipments/{$shipment->id}/receive")
        ->assertRedirect('/shipments');

    expect($shipment->fresh()->status)->toBe(ShipmentStatus::Received);

    $this->assertDatabaseHas('stock_items', [
        'branch_id' => $this->originBranch->id,
        'asset_id' => $this->asset->id,
        'quantity' => 7,
    ]);
    $this->assertDatabaseHas('stock_items', [
        'branch_id' => $this->destinationBranch->id,
        'asset_id' => $this->asset->id,
        'quantity' => 8,
    ]);
});

it('creates the destination stock item when it does not exist yet', function () {
    $logistica = User::factory()->logistica()->forBranch($this->destinationBranch)->create();
    $shipment = Shipment::factory()->forTransfer($this->transfer)->inTransit()->create();

    StockItem::factory()->for($this->originBranch)->for($this->asset)->create(['quantity' => 10]);

    $this->actingAs($logistica)
        ->patch("/shipments/{$shipment->id}/receive")
        ->assertRedirect('/shipments');

    $this->assertDatabaseHas('stock_items', [
        'branch_id' => $this->destinationBranch->id,
        'asset_id' => $this->asset->id,
        'quantity' => 5,
    ]);
});

it('denies receiving a shipment that is not in transit', function () {
    $logistica = User::factory()->logistica()->forBranch($this->destinationBranch)->create();
    $shipment = Shipment::factory()->forTransfer($this->transfer)->create();

    $this->actingAs($logistica)
        ->patch("/shipments/{$shipment->id}/receive")
        ->assertForbidden();
});
