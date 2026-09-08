<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Providers/Index', [
            // Never select api_key/api_secret/webhook_secret — those must not reach the frontend.
            'providers' => Provider::query()
                ->select(['id', 'name', 'driver', 'username', 'base_url', 'priority', 'is_active', 'consecutive_failures'])
                ->orderBy('priority')
                ->get(),
        ]);
    }

    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
            'priority' => ['required', 'integer', 'min:0'],
        ]);

        $provider->update($validated);

        return back()->with('success', 'Provider diperbarui.');
    }
}
