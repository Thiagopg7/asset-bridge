<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetRequestController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchStockController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('branches', BranchController::class)->except('show');
    Route::get('branches/{branch}/stock', [BranchStockController::class, 'index'])->name('branches.stock.index');
    Route::patch('branches/{branch}/stock/{asset}', [BranchStockController::class, 'update'])->name('branches.stock.update');

    Route::resource('assets', AssetController::class)->except('show');

    Route::resource('asset-requests', AssetRequestController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('asset-requests/{assetRequest}/approve', [AssetRequestController::class, 'approve'])->name('asset-requests.approve');
    Route::patch('asset-requests/{assetRequest}/reject', [AssetRequestController::class, 'reject'])->name('asset-requests.reject');

    Route::get('marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::post('marketplace/{assetRequest}/transfers', [TransferController::class, 'store'])->name('transfers.store');

    Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::patch('transfers/{transfer}/authorize', [TransferController::class, 'authorizeTransfer'])->name('transfers.authorize');
    Route::patch('transfers/{transfer}/reject', [TransferController::class, 'reject'])->name('transfers.reject');
    Route::delete('transfers/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy');

    Route::get('shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::patch('shipments/{shipment}/dispatch', [ShipmentController::class, 'dispatch'])->name('shipments.dispatch');
    Route::patch('shipments/{shipment}/receive', [ShipmentController::class, 'receive'])->name('shipments.receive');

    Route::resource('users', UserController::class)->except('show');
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
});

require __DIR__.'/settings.php';
