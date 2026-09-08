<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\LoyaltyPoint;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasAnyRole(['admin', 'cs'])) {
            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'totalUsers' => User::query()->count(),
                    'totalOmzet' => (float) Transaction::query()->where('status', Transaction::STATUS_SUCCESS)->sum('price'),
                    'successCount' => Transaction::query()->where('status', Transaction::STATUS_SUCCESS)->count(),
                    'failedCount' => Transaction::query()->where('status', Transaction::STATUS_FAILED)->count(),
                    'pendingDeposits' => Deposit::query()->where('status', Deposit::STATUS_PENDING)->count(),
                ],
                'recentTransactions' => Transaction::query()->with(['user', 'product'])->latest()->limit(10)->get(),
            ]);
        }

        $frequentProducts = Transaction::query()
            ->selectRaw('product_id, COUNT(*) as purchase_count')
            ->with('product.category')
            ->where('user_id', $user->id)
            ->where('status', Transaction::STATUS_SUCCESS)
            ->groupBy('product_id')
            ->orderByDesc('purchase_count')
            ->limit(3)
            ->get();

        $favoriteCategoryId = $user->favoriteProducts()
            ->with('product.category')
            ->get()
            ->groupBy(fn ($favorite) => $favorite->product?->category?->id)
            ->filter(fn ($favorites, $categoryId) => filled($categoryId))
            ->sortByDesc(fn ($favorites) => $favorites->count())
            ->keys()
            ->first();

        $favoriteProductIds = $user->favoriteProducts()->pluck('product_id');

        $recommendedProducts = $favoriteCategoryId
            ? Product::query()
                ->with('category')
                ->where('is_active', true)
                ->where('category_id', $favoriteCategoryId)
                ->whereNotIn('id', $favoriteProductIds)
                ->orderBy('name')
                ->limit(4)
                ->get()
            : collect();

        return Inertia::render('Dashboard', [
            'recentTransactions' => $user->transactions()->with('product')->latest()->limit(5)->get(),
            'favoriteProducts' => $user->favoriteProducts()->with('product')->latest()->limit(6)->get(),
            'loyaltyPointsBalance' => (int) LoyaltyPoint::query()->where('user_id', $user->id)->sum('points'),
            'promoBanners' => [
                [
                    'title' => 'Isi saldo lebih cepat',
                    'description' => 'Top up saldo sekarang untuk transaksi yang lebih lancar kapan saja.',
                    'cta' => 'Isi Saldo',
                    'href' => route('deposits.create'),
                    'tone' => 'from-primary-600 to-sky-500',
                ],
                [
                    'title' => 'Cek transaksi terakhir',
                    'description' => 'Pantau status pesanan dan ulangi pembelian dengan satu klik.',
                    'cta' => 'Lihat Transaksi',
                    'href' => route('transactions.index'),
                    'tone' => 'from-indigo-600 to-violet-500',
                ],
            ],
            'frequentProducts' => $frequentProducts,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }
}
