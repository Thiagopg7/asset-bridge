<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('branches', BranchController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
});

require __DIR__.'/settings.php';
