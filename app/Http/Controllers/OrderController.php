<?php

namespace App\Http\Controllers;

use App\Actions\Order\PlaceOrderAction;
use App\Models\Product;
use Illuminate\Http\Request;
use RuntimeException;

class OrderController extends Controller
{
    public function store(Request $request, PlaceOrderAction $action)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'target_number' => ['required', 'string', 'regex:/^[0-9]{5,20}$/'],
        ]);

        $product = Product::query()->where('is_active', true)->findOrFail($validated['product_id']);

        try {
            $transaction = $action->execute($request->user(), $product, $validated['target_number']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['target_number' => $e->getMessage()]);
        }

        return redirect()->route('transactions.show', $transaction)->with('success', 'Pesanan berhasil dibuat.');
    }
}
