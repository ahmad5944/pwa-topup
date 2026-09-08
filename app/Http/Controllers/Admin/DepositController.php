<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Deposit\CreditBalanceAction;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepositController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $deposits = Deposit::query()
            ->with('user')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Deposits/Index', [
            'deposits' => $deposits,
            'filters' => ['status' => $status],
        ]);
    }

    public function approve(Request $request, Deposit $deposit, CreditBalanceAction $action)
    {
        $action->execute($deposit, $request->user());

        return back()->with('success', 'Deposit disetujui.');
    }

    public function reject(Request $request, Deposit $deposit)
    {
        $deposit->update(['status' => Deposit::STATUS_FAILED, 'approved_by' => $request->user()->id]);

        return back()->with('success', 'Deposit ditolak.');
    }
}
