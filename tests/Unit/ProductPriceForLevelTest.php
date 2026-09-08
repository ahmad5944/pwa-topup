<?php

namespace Tests\Unit;

use App\Models\PriceLevel;
use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductPriceForLevelTest extends TestCase
{
    public function test_returns_base_price_when_no_price_level(): void
    {
        $product = new Product(['price_jual' => 1000]);

        $this->assertSame(1000.0, $product->priceForLevel(null));
    }

    public function test_applies_percentage_markup_on_top_of_base_price(): void
    {
        $product = new Product(['price_jual' => 1000]);
        $level = new PriceLevel(['markup_percent' => 10]);

        $this->assertSame(1100.0, $product->priceForLevel($level));
    }

    public function test_rounds_to_two_decimals(): void
    {
        $product = new Product(['price_jual' => 999]);
        $level = new PriceLevel(['markup_percent' => 3.33]);

        $this->assertSame(1032.27, $product->priceForLevel($level));
    }
}
