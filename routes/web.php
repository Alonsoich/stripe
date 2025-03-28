<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController; // Asegúrate de crear este controlador

// Ruta para mostrar el producto
Route::get('/', [StripeController::class, 'showProduct'])->name('product.show');

// Ruta para iniciar la sesión de Checkout de Stripe
Route::post('/checkout', [StripeController::class, 'checkout'])->name('checkout');

// Ruta para la página de éxito (después del pago)
Route::get('/success', [StripeController::class, 'success'])->name('success');

// Ruta para la página de cancelación (si el usuario cancela)
Route::get('/cancel', [StripeController::class, 'cancel'])->name('cancel');

// Ruta para manejar Webhooks de Stripe (Opcional pero recomendado para producción)
// Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');