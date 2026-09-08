<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommissionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Reseller/Commissions', [
            'commissions' => $request->user()->commissions()->with('transaction.product')->latest()->paginate(20),
        ]);
    }
}
