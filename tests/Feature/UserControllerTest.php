<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// index
it('allows an admin to list all users', function () {
    $admin = User::factory()->admin()->create();
    $branch = Branch::factory()->create();
    User::factory()->colaborador()->forBranch($branch)->count(3)->create();

    $this->actingAs($admin)
        ->get('/users')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/index')
            ->has('users.data', 4) // 3 colaboradores + o próprio admin
        );
});

it('allows a gerente to list only users from their branch', function () {
    $branch = Branch::factory()->create();
    $otherBranch = Branch::factory()->create();
    $gerente = User::factory()->gerente()->forBranch($branch)->create();
    User::factory()->colaborador()->forBranch($branch)->count(2)->create();
    User::factory()->colaborador()->forBranch($otherBranch)->count(3)->create();

    $this->actingAs($gerente)
        ->get('/users')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 3) // gerente + 2 colaboradores da mesma filial
        );
});

it('denies a colaborador from listing users', function () {
    $branch = Branch::factory()->create();
    $colaborador = User::factory()->colaborador()->forBranch($branch)->create();

    $this->actingAs($colaborador)->get('/users')->assertForbidden();
});

// create / store
it('allows an admin to create a user', function () {
    $admin = User::factory()->admin()->create();
    $branch = Branch::factory()->create();

    $this->actingAs($admin)->get('/users/create')->assertOk();

    $this->actingAs($admin)
        ->post('/users', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'colaborador',
            'branch_id' => $branch->id,
        ])
        ->assertRedirect('/users');

    $this->assertDatabaseHas('users', ['email' => 'novo@example.com']);
    expect(User::where('email', 'novo@example.com')->first()->hasRole('colaborador'))->toBeTrue();
});

it('validates required fields on store', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/users', [])
        ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

it('denies a gerente from creating users', function () {
    $branch = Branch::factory()->create();
    $gerente = User::factory()->gerente()->forBranch($branch)->create();

    $this->actingAs($gerente)->get('/users/create')->assertForbidden();
});

// edit / update
it('allows an admin to update a user', function () {
    $admin = User::factory()->admin()->create();
    $branch = Branch::factory()->create();
    $user = User::factory()->colaborador()->forBranch($branch)->create();

    $this->actingAs($admin)
        ->get("/users/{$user->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/edit')
            ->has('user')
            ->has('branches')
            ->has('roles')
        );

    $this->actingAs($admin)
        ->put("/users/{$user->id}", [
            'name' => 'Nome Atualizado',
            'email' => $user->email,
            'role' => 'gerente',
            'branch_id' => $branch->id,
        ])
        ->assertRedirect('/users');

    expect($user->fresh()->name)->toBe('Nome Atualizado');
    expect($user->fresh()->hasRole('gerente'))->toBeTrue();
});

// destroy
it('allows an admin to delete a user', function () {
    $admin = User::factory()->admin()->create();
    $branch = Branch::factory()->create();
    $user = User::factory()->colaborador()->forBranch($branch)->create();

    $this->actingAs($admin)
        ->delete("/users/{$user->id}")
        ->assertRedirect('/users');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('prevents an admin from deleting themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete("/users/{$admin->id}")->assertForbidden();
});

// roles page
it('allows an admin to view the roles matrix', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/roles')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('roles/index')
            ->has('roles')
            ->has('permissions')
        );
});

it('denies a colaborador from viewing the roles matrix', function () {
    $branch = Branch::factory()->create();
    $colaborador = User::factory()->colaborador()->forBranch($branch)->create();

    $this->actingAs($colaborador)->get('/roles')->assertForbidden();
});
