<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DownlineController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Reseller/Downlines', [
            'downlines' => $request->user()->downlines()->with('downline')->latest()->get(),
        ]);
    }
}
