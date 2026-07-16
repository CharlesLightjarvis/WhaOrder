<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'home/index')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('products', ProductController::class)->except('show');
    Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');

    Route::resource('categories', CategoryController::class)->except('show');

    Route::resource('customers', CustomerController::class)->except('show');

    Route::resource('addresses', AddressController::class)->except('show');
});

require __DIR__.'/settings.php';
