<?php

use App\Models\Asset;
use App\Models\Branch;
use App\Models\StockItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->branch = Branch::factory()->create();
    $this->otherBranch = Branch::factory()->create();
    $this->asset = Asset::factory()->create();
});

// index
it('allows admin to view stock of any branch', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get("/branches/{$this->branch->id}/stock")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('branches/stock')
            ->has('branch')
            ->has('stock')
        );

    $this->actingAs($user)
        ->get("/branches/{$this->otherBranch->id}/stock")
        ->assertOk();
});

it('allows gerente to view stock of their own branch', function () {
    $user = User::factory()->gerente()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->get("/branches/{$this->branch->id}/stock")
        ->assertOk();
});

it('denies gerente from viewing stock of another branch', function () {
    $user = User::factory()->gerente()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->get("/branches/{$this->otherBranch->id}/stock")
        ->assertForbidden();
});

it('allows colaborador to view stock of their own branch', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->get("/branches/{$this->branch->id}/stock")
        ->assertOk();
});

it('denies colaborador from viewing stock of another branch', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->get("/branches/{$this->otherBranch->id}/stock")
        ->assertForbidden();
});

// update
it('allows admin to update stock quantity', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->patch("/branches/{$this->branch->id}/stock/{$this->asset->id}", ['quantity' => 42])
        ->assertRedirect("/branches/{$this->branch->id}/stock");

    $this->assertDatabaseHas('stock_items', [
        'branch_id' => $this->branch->id,
        'asset_id' => $this->asset->id,
        'quantity' => 42,
    ]);
});

it('allows gerente to update stock of their own branch', function () {
    $user = User::factory()->gerente()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->patch("/branches/{$this->branch->id}/stock/{$this->asset->id}", ['quantity' => 10])
        ->assertRedirect("/branches/{$this->branch->id}/stock");

    expect(StockItem::where('branch_id', $this->branch->id)->first()->quantity)->toBe(10);
});

it('denies gerente from updating stock of another branch', function () {
    $user = User::factory()->gerente()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->patch("/branches/{$this->otherBranch->id}/stock/{$this->asset->id}", ['quantity' => 5])
        ->assertForbidden();
});

it('denies colaborador from updating stock', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->patch("/branches/{$this->branch->id}/stock/{$this->asset->id}", ['quantity' => 5])
        ->assertForbidden();
});

it('validates that quantity must be a non-negative integer', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->patch("/branches/{$this->branch->id}/stock/{$this->asset->id}", ['quantity' => -1])
        ->assertSessionHasErrors(['quantity']);

    $this->actingAs($user)
        ->patch("/branches/{$this->branch->id}/stock/{$this->asset->id}", ['quantity' => 'abc'])
        ->assertSessionHasErrors(['quantity']);
});

it('creates a stock item on first update and updates it on subsequent ones', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->patch("/branches/{$this->branch->id}/stock/{$this->asset->id}", ['quantity' => 5]);

    expect(StockItem::count())->toBe(1);

    $this->actingAs($user)
        ->patch("/branches/{$this->branch->id}/stock/{$this->asset->id}", ['quantity' => 20]);

    expect(StockItem::count())->toBe(1)
        ->and(StockItem::first()->quantity)->toBe(20);
});
