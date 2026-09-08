<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Reseller/Dashboard', [
            'stats' => [
                'downlineCount' => $user->downlines()->count(),
                'pendingCommission' => (float) $user->commissions()->where('status', Commission::STATUS_PENDING)->sum('amount'),
                'paidCommission' => (float) $user->commissions()->where('status', Commission::STATUS_PAID)->sum('amount'),
            ],
            'recentCommissions' => $user->commissions()->with('transaction.product')->latest()->limit(5)->get(),
        ]);
    }
}
