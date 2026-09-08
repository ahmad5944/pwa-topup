<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $products = Product::query()
            ->with(['category', 'provider'])
            ->when($search !== '', fn ($q) => $q->where('name', 'ilike', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => ['search' => $search],
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'price_jual' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $product->update($validated);

        return back()->with('success', 'Produk diperbarui.');
    }
}
