<?php

use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProviderController as AdminProviderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AppNotificationController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\FavoriteProductController;
use App\Http\Controllers\LoyaltyPointController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Reseller\CommissionController as ResellerCommissionController;
use App\Http\Controllers\Reseller\DashboardController as ResellerDashboardController;
use App\Http\Controllers\Reseller\DownlineController as ResellerDownlineController;
use App\Http\Controllers\TransactionController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');

    Route::post('/orders', [OrderController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('orders.store');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');

    Route::get('/points', [LoyaltyPointController::class, 'index'])->name('loyalty-points.index');

    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('/deposits', [DepositController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('deposits.store');

    Route::get('/favorites', [FavoriteProductController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FavoriteProductController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{favorite}', [FavoriteProductController::class, 'destroy'])->name('favorites.destroy');

    Route::get('/notifications', [AppNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [AppNotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::middleware('role:reseller')->prefix('reseller')->name('reseller.')->group(function () {
        Route::get('/', [ResellerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/downlines', [ResellerDownlineController::class, 'index'])->name('downlines');
        Route::get('/commissions', [ResellerCommissionController::class, 'index'])->name('commissions');
    });

    Route::middleware('role:admin|cs')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers.index');
        Route::patch('/providers/{provider}', [AdminProviderController::class, 'update'])->name('providers.update');

        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::patch('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/credit-balance', [AdminUserController::class, 'creditBalance'])->name('users.credit-balance');

        Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
        Route::post('/deposits/{deposit}/approve', [AdminDepositController::class, 'approve'])->name('deposits.approve');
        Route::post('/deposits/{deposit}/reject', [AdminDepositController::class, 'reject'])->name('deposits.reject');
    });
});

// Called by external providers; verified via HMAC/signature checks inside each controller, not sessions/CSRF.
Route::post('/webhooks/digiflazz', DigiflazzWebhookController::class)->name('webhooks.digiflazz');
Route::post('/webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');
