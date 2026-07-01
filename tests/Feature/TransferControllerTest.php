<?php

use App\Enums\TransferStatus;
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

// marketplace index
it('lists available offers from other branches only', function () {
    $user = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();

    AssetRequest::factory()->surplus()->approved()->for($this->destinationBranch)->for($this->asset)
        ->create(['quantity' => 5, 'available_quantity' => 5]);
    AssetRequest::factory()->surplus()->approved()->for($this->offerBranch)->for($this->asset)
        ->create(['quantity' => 5, 'available_quantity' => 0]);

    $this->actingAs($user)
        ->get('/marketplace')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('marketplace/index')
            ->has('offers.data', 1)
            ->where('offers.data.0.id', $this->offer->id)
        );
});

// store
it('lets a collaborator request a transfer against another branch offer', function () {
    $user = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();

    $this->actingAs($user)
        ->post("/marketplace/{$this->offer->id}/transfers", [
            'quantity' => 8,
            'notes' => 'Precisamos para o projeto.',
        ])
        ->assertRedirect('/transfers');

    $this->assertDatabaseHas('transfers', [
        'asset_request_id' => $this->offer->id,
        'branch_id' => $this->destinationBranch->id,
        'user_id' => $user->id,
        'quantity' => 8,
        'status' => TransferStatus::Pending->value,
    ]);
});

it('denies requesting a transfer from the own branch offer', function () {
    $user = User::factory()->colaborador()->forBranch($this->offerBranch)->create();

    $this->actingAs($user)
        ->post("/marketplace/{$this->offer->id}/transfers", ['quantity' => 5])
        ->assertSessionHasErrors('quantity');

    expect(Transfer::count())->toBe(0);
});

it('rejects a transfer request greater than the offer balance', function () {
    $user = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();

    $this->actingAs($user)
        ->post("/marketplace/{$this->offer->id}/transfers", ['quantity' => 25])
        ->assertSessionHasErrors('quantity');

    expect(Transfer::count())->toBe(0);
});

it('rejects a transfer request with a quantity below one', function () {
    $user = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();

    $this->actingAs($user)
        ->post("/marketplace/{$this->offer->id}/transfers", ['quantity' => 0])
        ->assertSessionHasErrors('quantity');

    expect(Transfer::count())->toBe(0);
});

it('denies a user without a branch from requesting a transfer', function () {
    $user = User::factory()->colaborador()->create();

    $this->actingAs($user)
        ->post("/marketplace/{$this->offer->id}/transfers", ['quantity' => 5])
        ->assertForbidden();
});

// authorize
it('lets the destination manager authorize and draws down the offer balance', function () {
    $gerente = User::factory()->gerente()->forBranch($this->destinationBranch)->create();
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->create(['quantity' => 8]);

    $this->actingAs($gerente)
        ->patch("/transfers/{$transfer->id}/authorize")
        ->assertRedirect('/transfers');

    expect($transfer->fresh()->status)->toBe(TransferStatus::Authorized);
    expect($this->offer->fresh()->available_quantity)->toBe(12);

    $this->assertDatabaseHas('shipments', [
        'transfer_id' => $transfer->id,
        'origin_branch_id' => $this->offerBranch->id,
        'destination_branch_id' => $this->destinationBranch->id,
        'status' => 'ready',
    ]);
});

it('does not authorize a transfer that exceeds the remaining balance', function () {
    $gerente = User::factory()->gerente()->forBranch($this->destinationBranch)->create();
    $this->offer->update(['available_quantity' => 5]);
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->create(['quantity' => 8]);

    $this->actingAs($gerente)
        ->patch("/transfers/{$transfer->id}/authorize");

    expect($transfer->fresh()->status)->toBe(TransferStatus::Pending);
    expect($this->offer->fresh()->available_quantity)->toBe(5);
});

it('denies the offering branch manager from authorizing the transfer', function () {
    $gerente = User::factory()->gerente()->forBranch($this->offerBranch)->create();
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->create();

    $this->actingAs($gerente)
        ->patch("/transfers/{$transfer->id}/authorize")
        ->assertForbidden();
});

// reject
it('lets the destination manager reject a transfer without touching the balance', function () {
    $gerente = User::factory()->gerente()->forBranch($this->destinationBranch)->create();
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->create(['quantity' => 8]);

    $this->actingAs($gerente)
        ->patch("/transfers/{$transfer->id}/reject")
        ->assertRedirect('/transfers');

    expect($transfer->fresh()->status)->toBe(TransferStatus::Rejected);
    expect($this->offer->fresh()->available_quantity)->toBe(20);
});

// destroy
it('lets the author cancel their own pending transfer', function () {
    $user = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->for($user)->create();

    $this->actingAs($user)
        ->delete("/transfers/{$transfer->id}")
        ->assertRedirect('/transfers');

    $this->assertDatabaseMissing('transfers', ['id' => $transfer->id]);
});

it('denies a user from cancelling a transfer they did not create', function () {
    $user = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();
    $other = User::factory()->colaborador()->forBranch($this->destinationBranch)->create();
    $transfer = Transfer::factory()->for($this->offer)->for($this->destinationBranch)->for($other)->create();

    $this->actingAs($user)
        ->delete("/transfers/{$transfer->id}")
        ->assertForbidden();
});
