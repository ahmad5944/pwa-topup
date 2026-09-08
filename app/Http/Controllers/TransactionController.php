<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $transactions = $request->user()->transactions()
            ->with('product')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => ['status' => $status],
        ]);
    }

    public function show(Request $request, Transaction $transaction): Response
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction->load(['product.category', 'logs']),
        ]);
    }
}
