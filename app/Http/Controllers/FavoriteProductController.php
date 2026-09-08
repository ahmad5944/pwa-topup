<?php

namespace App\Http\Controllers;

use App\Models\FavoriteProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteProductController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Favorites/Index', [
            'favorites' => $request->user()->favoriteProducts()->with('product.category')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'target_number' => ['nullable', 'string', 'max:20'],
        ]);

        $request->user()->favoriteProducts()->firstOrCreate($validated);

        return back()->with('success', 'Ditambahkan ke favorit.');
    }

    public function destroy(Request $request, FavoriteProduct $favorite)
    {
        abort_unless($favorite->user_id === $request->user()->id, 403);

        $favorite->delete();

        return back()->with('success', 'Dihapus dari favorit.');
    }
}
