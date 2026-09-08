<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'category_id',
        'buyer_sku_code',
        'name',
        'brand',
        'type',
        'price_beli',
        'price_jual',
        'unlimited_stock',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_beli' => 'decimal:2',
            'price_jual' => 'decimal:2',
            'unlimited_stock' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** Selling price for a given price level, applying its markup on top of price_jual. */
    public function priceForLevel(?PriceLevel $level): float
    {
        $markup = $level?->markup_percent ?? 0;

        return round((float) $this->price_jual * (1 + $markup / 100), 2);
    }
}
