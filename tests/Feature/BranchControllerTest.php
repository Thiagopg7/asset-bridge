<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// index
it('allows an admin to list branches', function () {
    $user = User::factory()->admin()->create();
    Branch::factory()->count(2)->create();

    $this->actingAs($user)
        ->get('/branches')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('branches/index')
            ->has('branches.data', 2)
        );
});

it('allows a colaborador to list branches but not manage them', function () {
    $branch = Branch::factory()->create();
    $user = User::factory()->colaborador()->forBranch($branch)->create();

    $this->actingAs($user)->get('/branches')->assertOk();
    $this->actingAs($user)->get('/branches/create')->assertForbidden();
    $this->actingAs($user)->delete("/branches/{$branch->id}")->assertForbidden();
});

// create / store
it('allows an admin to create a branch', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get('/branches/create')
        ->assertOk();

    $this->actingAs($user)
        ->post('/branches', [
            'name' => 'Filial Teste',
            'code' => 'FIL-TST',
            'city' => 'Campinas',
            'state' => 'SP',
            'active' => true,
        ])
        ->assertRedirect('/branches');

    $this->assertDatabaseHas('branches', ['code' => 'FIL-TST']);
});

it('validates required fields on store', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post('/branches', [])
        ->assertSessionHasErrors(['name', 'code']);
});

it('denies a gerente from creating branches', function () {
    $branch = Branch::factory()->create();
    $user = User::factory()->gerente()->forBranch($branch)->create();

    $this->actingAs($user)->get('/branches/create')->assertForbidden();
});

// edit / update
it('allows an admin to update a branch', function () {
    $user = User::factory()->admin()->create();
    $branch = Branch::factory()->create();

    $this->actingAs($user)
        ->get("/branches/{$branch->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('branches/edit')
            ->has('branch')
        );

    $this->actingAs($user)
        ->put("/branches/{$branch->id}", [
            'name' => 'Atualizada',
            'code' => $branch->code,
            'active' => true,
        ])
        ->assertRedirect('/branches');

    expect($branch->fresh()->name)->toBe('Atualizada');
});

// destroy
it('allows an admin to delete a branch', function () {
    $user = User::factory()->admin()->create();
    $branch = Branch::factory()->create();

    $this->actingAs($user)
        ->delete("/branches/{$branch->id}")
        ->assertRedirect('/branches');

    $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
});

it('denies a colaborador from deleting branches', function () {
    $branch = Branch::factory()->create();
    $user = User::factory()->colaborador()->forBranch($branch)->create();

    $this->actingAs($user)->delete("/branches/{$branch->id}")->assertForbidden();
});
