<?php

use App\Enums\AssetRequestStatus;
use App\Models\Asset;
use App\Models\AssetRequest;
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
it('lists only requests from the collaborator own branch', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    $mine = AssetRequest::factory()->for($this->branch)->for($this->asset)->create();
    AssetRequest::factory()->for($this->otherBranch)->for($this->asset)->create();

    $this->actingAs($user)
        ->get('/asset-requests')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('asset-requests/index')
            ->has('requests.data', 1)
            ->where('requests.data.0.id', $mine->id)
        );
});

it('lists requests from all branches for an admin', function () {
    $user = User::factory()->admin()->create();
    AssetRequest::factory()->for($this->branch)->for($this->asset)->create();
    AssetRequest::factory()->for($this->otherBranch)->for($this->asset)->create();

    $this->actingAs($user)
        ->get('/asset-requests')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('requests.data', 2));
});

// store
it('allows a collaborator to open a need request for their branch', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->post('/asset-requests', [
            'asset_id' => $this->asset->id,
            'type' => 'need',
            'quantity' => 5,
            'notes' => 'Preciso para a obra.',
        ])
        ->assertRedirect('/asset-requests');

    $this->assertDatabaseHas('asset_requests', [
        'branch_id' => $this->branch->id,
        'user_id' => $user->id,
        'asset_id' => $this->asset->id,
        'type' => 'need',
        'quantity' => 5,
        'status' => AssetRequestStatus::Pending->value,
    ]);
});

it('denies a user without a branch from creating a request', function () {
    $user = User::factory()->colaborador()->create();

    $this->actingAs($user)
        ->post('/asset-requests', [
            'asset_id' => $this->asset->id,
            'type' => 'need',
            'quantity' => 5,
        ])
        ->assertForbidden();
});

it('validates required fields when creating a request', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->post('/asset-requests', ['type' => 'invalid', 'quantity' => 0])
        ->assertSessionHasErrors(['asset_id', 'type', 'quantity']);
});

it('rejects a surplus offer greater than the branch available stock', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    StockItem::factory()->for($this->branch)->for($this->asset)->create(['quantity' => 3]);

    $this->actingAs($user)
        ->post('/asset-requests', [
            'asset_id' => $this->asset->id,
            'type' => 'surplus',
            'quantity' => 10,
        ])
        ->assertSessionHasErrors(['quantity']);

    expect(AssetRequest::count())->toBe(0);
});

it('allows a surplus offer within the branch available stock', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    StockItem::factory()->for($this->branch)->for($this->asset)->create(['quantity' => 10]);

    $this->actingAs($user)
        ->post('/asset-requests', [
            'asset_id' => $this->asset->id,
            'type' => 'surplus',
            'quantity' => 4,
        ])
        ->assertRedirect('/asset-requests');

    expect(AssetRequest::count())->toBe(1);
});

// show
it('lets a gerente view the details of a request from their branch', function () {
    $gerente = User::factory()->gerente()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->need()->for($this->branch)->for($this->asset)->create();

    $this->actingAs($gerente)
        ->get("/asset-requests/{$request->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('asset-requests/show')
            ->where('request.id', $request->id)
            ->where('request.can_review', true)
        );
});

it('denies viewing the details of a request from another branch', function () {
    $gerente = User::factory()->gerente()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->need()->for($this->otherBranch)->for($this->asset)->create();

    $this->actingAs($gerente)
        ->get("/asset-requests/{$request->id}")
        ->assertForbidden();
});

// edit / update
it('allows the author to edit their own pending request', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->need()->for($this->branch)->for($this->asset)->for($user)
        ->create(['quantity' => 5]);

    $this->actingAs($user)
        ->patch("/asset-requests/{$request->id}", [
            'asset_id' => $this->asset->id,
            'type' => 'need',
            'quantity' => 12,
            'notes' => 'Quantidade revisada.',
        ])
        ->assertRedirect('/asset-requests');

    expect($request->fresh())
        ->quantity->toBe(12)
        ->notes->toBe('Quantidade revisada.');
});

it('denies editing a request that was already reviewed', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->need()->approved()->for($this->branch)->for($this->asset)->for($user)
        ->create(['quantity' => 5]);

    $this->actingAs($user)
        ->patch("/asset-requests/{$request->id}", [
            'asset_id' => $this->asset->id,
            'type' => 'need',
            'quantity' => 12,
        ])
        ->assertForbidden();

    expect($request->fresh()->quantity)->toBe(5);
});

it('denies a user from editing a request they did not create', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    $other = User::factory()->colaborador()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->need()->for($this->branch)->for($this->asset)->for($other)
        ->create(['quantity' => 5]);

    $this->actingAs($user)
        ->patch("/asset-requests/{$request->id}", [
            'asset_id' => $this->asset->id,
            'type' => 'need',
            'quantity' => 12,
        ])
        ->assertForbidden();
});

// approve / reject
it('allows a gerente to approve a request from their own branch', function () {
    $gerente = User::factory()->gerente()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->branch)->for($this->asset)->create();

    $this->actingAs($gerente)
        ->patch("/asset-requests/{$request->id}/approve")
        ->assertRedirect('/asset-requests');

    expect($request->fresh())
        ->status->toBe(AssetRequestStatus::Approved)
        ->reviewed_by->toBe($gerente->id);
});

it('denies a gerente from approving a request of another branch', function () {
    $gerente = User::factory()->gerente()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->otherBranch)->for($this->asset)->create();

    $this->actingAs($gerente)
        ->patch("/asset-requests/{$request->id}/approve")
        ->assertForbidden();
});

it('denies a collaborator from approving a request', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->branch)->for($this->asset)->create();

    $this->actingAs($user)
        ->patch("/asset-requests/{$request->id}/approve")
        ->assertForbidden();
});

it('cannot review a request that is no longer pending', function () {
    $gerente = User::factory()->gerente()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->branch)->for($this->asset)->approved()->create();

    $this->actingAs($gerente)
        ->patch("/asset-requests/{$request->id}/reject")
        ->assertForbidden();
});

it('allows a gerente to reject a request from their own branch', function () {
    $gerente = User::factory()->gerente()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->branch)->for($this->asset)->create();

    $this->actingAs($gerente)
        ->patch("/asset-requests/{$request->id}/reject")
        ->assertRedirect('/asset-requests');

    expect($request->fresh()->status)->toBe(AssetRequestStatus::Rejected);
});

// destroy
it('allows the author to cancel their own pending request', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->branch)->for($this->asset)->for($user)->create();

    $this->actingAs($user)
        ->delete("/asset-requests/{$request->id}")
        ->assertRedirect('/asset-requests');

    $this->assertDatabaseMissing('asset_requests', ['id' => $request->id]);
});

it('denies a user from cancelling a request they did not create', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();
    $other = User::factory()->colaborador()->forBranch($this->branch)->create();
    $request = AssetRequest::factory()->for($this->branch)->for($this->asset)->for($other)->create();

    $this->actingAs($user)
        ->delete("/asset-requests/{$request->id}")
        ->assertForbidden();
});
