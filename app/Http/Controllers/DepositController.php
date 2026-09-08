<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Services\Payments\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DepositController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Deposits/Index', [
            'deposits' => $request->user()->deposits()->latest()->paginate(20),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Deposits/Create');
    }

    public function store(Request $request, MidtransService $midtrans)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:10000'],
        ]);

        $user = $request->user();
        $orderId = 'DEP-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'method' => 'midtrans',
            'status' => Deposit::STATUS_PENDING,
            'order_id' => $orderId,
        ]);

        $snap = $midtrans->createSnapTransaction($orderId, (int) $validated['amount'], [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        return response()->json([
            'deposit' => $deposit,
            'snap_token' => $snap['token'] ?? null,
            'redirect_url' => $snap['redirect_url'] ?? null,
        ]);
    }
}
