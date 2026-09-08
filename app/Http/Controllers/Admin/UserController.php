<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->with('priceLevel')
            ->when($search !== '', fn ($q) => $q->where('name', 'ilike', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'priceLevels' => PriceLevel::query()->orderBy('name')->get(),
            'filters' => ['search' => $search],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'price_level_id' => ['nullable', 'exists:price_levels,id'],
        ]);

        $user->update($validated);

        return back()->with('success', 'User diperbarui.');
    }

    /** Manual balance credit by an admin — locks the row to stay consistent with other balance mutations. */
    public function creditBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1000'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            User::query()->lockForUpdate()->find($user->id)?->increment('balance', $validated['amount']);
        });

        return back()->with('success', 'Saldo berhasil ditambahkan.');
    }
}
