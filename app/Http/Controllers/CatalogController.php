<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        $search = $request->string('search')->toString();
        $categorySlug = $request->string('category')->toString();

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($categorySlug !== '', fn ($q) => $q->whereHas('category', fn ($q2) => $q2->where('slug', $categorySlug)))
            ->when($search !== '', fn ($q) => $q->where('name', 'ilike', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $priceLevel = $request->user()?->priceLevel;

        $products->getCollection()->transform(function (Product $product) use ($priceLevel) {
            $product->display_price = $product->priceForLevel($priceLevel);

            return $product;
        });

        return Inertia::render('Catalog/Index', [
            'categories' => $categories,
            'products' => $products,
            'filters' => ['category' => $categorySlug, 'search' => $search],
        ]);
    }

    public function show(Request $request, Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load('category');
        $product->display_price = $product->priceForLevel($request->user()?->priceLevel);

        $favoriteTargetNumbers = $request->user()?->favoriteProducts()
            ->where('product_id', $product->id)
            ->whereNotNull('target_number')
            ->orderByDesc('updated_at')
            ->pluck('target_number')
            ->unique()
            ->values();

        return Inertia::render('Catalog/Show', [
            'product' => $product,
            'favoriteTargetNumbers' => $favoriteTargetNumbers,
            'suggestedTargetNumber' => $request->string('target_number')->toString() ?: ($favoriteTargetNumbers->first() ?? ''),
        ]);
    }
}
