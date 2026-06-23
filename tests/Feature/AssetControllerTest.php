<?php

use App\Models\Asset;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// index
it('allows any role to list assets', function () {
    Asset::factory()->count(3)->create();
    $user = User::factory()->colaborador()->create();

    $this->actingAs($user)
        ->get('/assets')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('assets/index')
            ->has('assets.data', 3)
        );
});

it('denies unauthenticated access to assets', function () {
    $this->get('/assets')->assertRedirect('/login');
});

// create / store
it('allows admin to create an asset', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->get('/assets/create')->assertOk();

    $this->actingAs($user)
        ->post('/assets', [
            'name' => 'Cabo de Rede Cat6',
            'unit' => 'un',
            'active' => true,
        ])
        ->assertRedirect('/assets');

    $this->assertDatabaseHas('assets', ['name' => 'Cabo de Rede Cat6']);
});

it('allows diretor to create an asset', function () {
    $user = User::factory()->diretor()->create();

    $this->actingAs($user)
        ->post('/assets', ['name' => 'Cadeira', 'unit' => 'un', 'active' => true])
        ->assertRedirect('/assets');
});

it('validates required fields on store', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post('/assets', [])
        ->assertSessionHasErrors(['name', 'unit']);
});

it('denies gerente from creating assets', function () {
    $user = User::factory()->gerente()->create();

    $this->actingAs($user)->get('/assets/create')->assertForbidden();
    $this->actingAs($user)
        ->post('/assets', ['name' => 'Mesa', 'unit' => 'un'])
        ->assertForbidden();
});

// edit / update
it('allows admin to update an asset', function () {
    $user = User::factory()->admin()->create();
    $asset = Asset::factory()->create(['name' => 'Original']);

    $this->actingAs($user)
        ->get("/assets/{$asset->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('assets/edit')->has('asset'));

    $this->actingAs($user)
        ->put("/assets/{$asset->id}", ['name' => 'Atualizado', 'unit' => 'cx', 'active' => true])
        ->assertRedirect('/assets');

    expect($asset->fresh()->name)->toBe('Atualizado');
});

// destroy
it('allows admin to delete an asset', function () {
    $user = User::factory()->admin()->create();
    $asset = Asset::factory()->create();

    $this->actingAs($user)
        ->delete("/assets/{$asset->id}")
        ->assertRedirect('/assets');

    $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
});

it('denies colaborador from deleting assets', function () {
    $asset = Asset::factory()->create();
    $user = User::factory()->colaborador()->create();

    $this->actingAs($user)->delete("/assets/{$asset->id}")->assertForbidden();
});
