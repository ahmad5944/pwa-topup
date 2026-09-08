<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('Account/Show', [
            'loyaltyPointsBalance' => (int) LoyaltyPoint::query()->where('user_id', $request->user()->id)->sum('points'),
        ]);
    }
}
