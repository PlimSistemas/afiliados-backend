<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\Cashback;
use App\Models\CashbackHistory;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50',
            'method' => 'required|in:pix,invoice_credit',
        ]);

        $user   = $this->auth()->user();
        $amount = $request->amount;

        try {
            DB::beginTransaction();

            $cashback = Cashback::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$cashback || $cashback->available_balance < $amount) {
                DB::rollBack();
                return response()->json(['error' => 'Saldo insuficiente.'], 400);
            }

            $cashback->available_balance -= $amount;
            $cashback->blocked_balance   += $amount;
            $cashback->save();

            $withdrawal = Withdrawal::create([
                'user_id'  => $user->id,
                'amount'   => $amount,
                'method'   => $request->method,
                'pix_key'  => $request->pixKey ?? $user->pix_key,
                'status'   => 'pending',
            ]);

            CashbackHistory::create([
                'user_id'       => $user->id,
                'referral_name' => 'Pedido de Saque',
                'value'         => $amount,
                'is_positive'   => false,
                'date'          => now(),
            ]);

            DB::commit();

            return response()->json($withdrawal, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao processar o saque.'], 500);
        }
    }

    public function history(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $query   = Withdrawal::where('user_id', $this->auth()->id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->orderByDesc('requested_at')->paginate($perPage)
        );
    }

    public function stats()
    {
        $base = Withdrawal::where('user_id', $this->auth()->id());

        return response()->json([
            'total'          => (clone $base)->count(),
            'total_amount'   => (clone $base)->sum('amount'),
            'paid_count'     => (clone $base)->where('status', 'paid')->count(),
            'rejected_count' => (clone $base)->where('status', 'rejected')->count(),
            'approved_count' => (clone $base)->where('status', 'approved')->count(),
            'pending_count'  => (clone $base)->where('status', 'pending')->count(),
        ]);
    }
}
