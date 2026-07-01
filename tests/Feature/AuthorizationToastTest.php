<?php

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->branch = Branch::factory()->create();
});

it('keeps returning 403 for non-inertia requests', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->get('/branches')
        ->assertForbidden();
});

it('redirects inertia actions with a toast instead of a 403 page', function () {
    $user = User::factory()->colaborador()->forBranch($this->branch)->create();

    $this->actingAs($user)
        ->delete("/branches/{$this->branch->id}", [], ['X-Inertia' => 'true'])
        ->assertRedirect();
});
