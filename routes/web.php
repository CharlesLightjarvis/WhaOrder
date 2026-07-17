<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WhatsAppSessionController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'home/index')->name('home');

Route::post('webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('webhooks.whatsapp');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('products', ProductController::class)->except('show');
    Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');

    Route::resource('categories', CategoryController::class)->except('show');

    Route::resource('customers', CustomerController::class)->except('show');

    Route::resource('addresses', AddressController::class)->except('show');

    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::patch('payment-proofs/{paymentProof}/confirm', [PaymentProofController::class, 'confirm'])->name('payment-proofs.confirm');
    Route::patch('payment-proofs/{paymentProof}/reject', [PaymentProofController::class, 'reject'])->name('payment-proofs.reject');

    Route::resource('conversations', ConversationController::class)->only(['index', 'show']);

    Route::resource('whatsapp-sessions', WhatsAppSessionController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['whatsapp-sessions' => 'whatsAppSession']);
    Route::patch('whatsapp-sessions/{whatsAppSession}/refresh', [WhatsAppSessionController::class, 'refresh'])->name('whatsapp-sessions.refresh');
});

require __DIR__.'/settings.php';
