<?php

use Illuminate\Support\Facades\Route;
use Modules\Unit\Http\Controllers\Admin\UnitController;

Route::group([
    'prefix' => 'admin/units',
    'as' => 'admin.units.',
    'middleware' => ['web', 'auth:admin', 'can:admin.products.index'],
], function () {
    Route::get('/', [UnitController::class, 'index'])->name('index');
    Route::get('/create', [UnitController::class, 'create'])->name('create');
    Route::post('/', [UnitController::class, 'store'])->name('store');
    Route::get('/{unit}/edit', [UnitController::class, 'edit'])->name('edit');
    Route::put('/{unit}', [UnitController::class, 'update'])->name('update');
    Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('destroy');
});
