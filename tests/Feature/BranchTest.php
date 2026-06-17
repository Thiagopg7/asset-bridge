<?php

use App\Models\Branch;
use App\Models\User;

it('assigns a user to a single branch', function () {
    $branch = Branch::factory()->create();
    $user = User::factory()->forBranch($branch)->create();

    expect($user->branch->is($branch))->toBeTrue();
    expect($branch->users()->whereKey($user->id)->exists())->toBeTrue();
    expect($user->belongsToBranch($branch))->toBeTrue();
});

it('allows a global user without a branch', function () {
    $user = User::factory()->admin()->create();

    expect($user->branch_id)->toBeNull();
    expect($user->branch)->toBeNull();
});

it('nullifies branch_id when the branch is deleted', function () {
    $branch = Branch::factory()->create();
    $user = User::factory()->forBranch($branch)->create();

    $branch->delete();

    expect($user->fresh()->branch_id)->toBeNull();
});

it('casts active to boolean', function () {
    $branch = Branch::factory()->inactive()->create();

    expect($branch->active)->toBeFalse();
});
