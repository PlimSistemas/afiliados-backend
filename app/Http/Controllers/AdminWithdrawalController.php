<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\Cashback;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $query   = Withdrawal::with('user:id,name,cpf,pix_key');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->orderByDesc('requested_at')->paginate($perPage)
        );
    }

    public function stats()
    {
        return response()->json([
            'total_pending'  => Withdrawal::where('status', 'pending')->count(),
            'total_paid'     => Withdrawal::where('status', 'paid')->count(),
            'total_rejected' => Withdrawal::where('status', 'rejected')->count(),
            'sum_pending'    => Withdrawal::where('status', 'pending')->sum('amount'),
            'sum_paid'       => Withdrawal::where('status', 'paid')->sum('amount'),
        ]);
    }

    public function approve($id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        if ($withdrawal->status !== 'pending') {
            return response()->json(['error' => 'Saque não está pendente.'], 400);
        }

        $withdrawal->status = 'approved';
        $withdrawal->processed_at = now();
        $withdrawal->save();

        return response()->json($withdrawal);
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'approved') {
            return response()->json(['error' => 'Status inválido para rejeição.'], 400);
        }

        try {
            DB::beginTransaction();

            $withdrawal->status = 'rejected';
            $withdrawal->rejection_reason = $request->reason;
            $withdrawal->processed_at = now();
            $withdrawal->save();

            $cashback = Cashback::where('user_id', $withdrawal->user_id)->lockForUpdate()->first();
            if ($cashback) {
                $cashback->blocked_balance -= $withdrawal->amount;
                $cashback->available_balance += $withdrawal->amount;
                $cashback->save();
            }

            DB::commit();
            return response()->json($withdrawal);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro interno.'], 500);
        }
    }

    public function markPaid($id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        if ($withdrawal->status !== 'approved') {
            return response()->json(['error' => 'Saque não está aprovado.'], 400);
        }

        try {
            DB::beginTransaction();

            $withdrawal->status = 'paid';
            $withdrawal->paid_at = now();
            $withdrawal->save();

            $cashback = Cashback::where('user_id', $withdrawal->user_id)->lockForUpdate()->first();
            if ($cashback) {
                $cashback->blocked_balance -= $withdrawal->amount;
                $cashback->save();
            }

            DB::commit();
            return response()->json($withdrawal);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro interno.'], 500);
        }
    }

    public function showUser($id)
    {
        $user = User::select('id', 'name', 'cpf', 'pix_key')->findOrFail($id);
        return response()->json($user);
    }
}
