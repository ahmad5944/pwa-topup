<?php

use App\Http\Controllers\DepositController;
use App\Http\Controllers\Webhooks\DigiflazzWebhookController;
use App\Http\Controllers\Webhooks\MidtransWebhookController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::post('/deposits', [DepositController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('deposits.store');
});

// Called by external providers; verified via HMAC/signature checks inside each controller, not sessions/CSRF.
Route::post('/webhooks/digiflazz', DigiflazzWebhookController::class)->name('webhooks.digiflazz');
Route::post('/webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');
