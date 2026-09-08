<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\Category;
use App\Models\Commission;
use App\Models\Deposit;
use App\Models\FavoriteProduct;
use App\Models\PriceLevel;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ResellerDownline;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Populate the app with realistic-looking demo data. Never dispatches jobs
     * or talks to real providers/payment gateways — statuses are set directly.
     */
    public function run(): void
    {
        $priceLevels = collect([
            PriceLevel::factory()->create(['name' => 'Member', 'markup_percent' => 0, 'is_reseller_level' => false]),
            PriceLevel::factory()->create(['name' => 'Silver Reseller', 'markup_percent' => 3, 'is_reseller_level' => true]),
            PriceLevel::factory()->create(['name' => 'Gold Reseller', 'markup_percent' => 1.5, 'is_reseller_level' => true]),
        ]);

        $memberLevel = $priceLevels->firstWhere('name', 'Member');
        $resellerLevels = $priceLevels->where('is_reseller_level', true)->values();

        $providers = Provider::factory()->count(3)->create();

        $categories = collect(['Pulsa', 'Paket Data', 'Token PLN', 'Voucher Game', 'E-Wallet', 'Voucher Streaming'])
            ->map(fn ($name) => Category::factory()->create(['name' => $name, 'slug' => Str::slug($name)]));

        $products = collect();
        foreach ($categories as $category) {
            $products = $products->merge(
                Product::factory()
                    ->count(10)
                    ->state(fn () => ['provider_id' => $providers->random()->id])
                    ->create(['category_id' => $category->id])
            );
        }

        $cs = User::factory()->count(2)->create()->each(fn (User $user) => $user->assignRole('cs'));

        $resellers = User::factory()
            ->count(5)
            ->state(fn () => ['price_level_id' => $resellerLevels->random()->id])
            ->create()
            ->each(fn (User $user) => $user->assignRole('reseller'));

        $members = User::factory()
            ->count(15)
            ->state(fn () => ['price_level_id' => $memberLevel->id])
            ->create()
            ->each(fn (User $user) => $user->assignRole('member'));

        foreach ($resellers as $index => $reseller) {
            foreach ($members->slice($index * 3, 3) as $downlineMember) {
                ResellerDownline::factory()->create([
                    'reseller_id' => $reseller->id,
                    'downline_user_id' => $downlineMember->id,
                ]);
            }
        }

        // Includes the admin user created earlier in DatabaseSeeder.
        $allUsers = User::all();

        $transactions = collect();
        foreach ($allUsers as $user) {
            $transactions = $transactions->merge(
                Transaction::factory()
                    ->count(fake()->numberBetween(3, 12))
                    ->state(fn () => ['product_id' => $products->random()->id])
                    ->create(['user_id' => $user->id])
            );
        }

        foreach ($transactions as $transaction) {
            TransactionLog::factory()
                ->count(fake()->numberBetween(1, 3))
                ->create(['transaction_id' => $transaction->id]);
        }

        foreach ($resellers as $reseller) {
            $downlineIds = ResellerDownline::query()->where('reseller_id', $reseller->id)->pluck('downline_user_id');

            $transactions
                ->whereIn('user_id', $downlineIds)
                ->where('status', Transaction::STATUS_SUCCESS)
                ->each(fn (Transaction $transaction) => Commission::factory()->create([
                    'reseller_id' => $reseller->id,
                    'transaction_id' => $transaction->id,
                ]));
        }

        foreach ($allUsers as $user) {
            Deposit::factory()
                ->count(fake()->numberBetween(1, 3))
                ->create(['user_id' => $user->id]);

            FavoriteProduct::factory()
                ->count(fake()->numberBetween(0, 4))
                ->state(fn () => ['product_id' => $products->random()->id])
                ->create(['user_id' => $user->id]);

            AppNotification::factory()
                ->count(fake()->numberBetween(2, 6))
                ->create(['user_id' => $user->id]);
        }
    }
}
