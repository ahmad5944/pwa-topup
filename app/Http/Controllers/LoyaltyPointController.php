<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyPointController extends Controller
{
    public function index(Request $request): Response
    {
        $points = $request->user()->loyaltyPoints()
            ->with('transaction.product')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('LoyaltyPoints/Index', [
            'points' => $points,
            'balance' => (int) $request->user()->loyaltyPoints()->sum('points'),
        ]);
    }
}
