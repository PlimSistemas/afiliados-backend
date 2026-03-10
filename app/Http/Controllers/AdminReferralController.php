<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referral;
use App\Models\Cashback;
use App\Models\CashbackHistory;
use Illuminate\Support\Facades\DB;

class AdminReferralController extends Controller
{
    private const VALID_STATUSES = [
        'awaiting',
        'contracted',
        'awaiting_payment',
        'cashback_released',
        'paid',
        'rejected',
    ];

    /** Lista todas as indicações (todos os usuários) com filtro opcional de status */
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $query   = Referral::with('referrer:id,name,cpf,referral_code');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }

    /** Estatísticas globais de indicações */
    public function stats()
    {
        return response()->json([
            'total'             => Referral::count(),
            'awaiting'          => Referral::where('status', 'awaiting')->count(),
            'contracted'        => Referral::where('status', 'contracted')->count(),
            'awaiting_payment'  => Referral::where('status', 'awaiting_payment')->count(),
            'cashback_released' => Referral::where('status', 'cashback_released')->count(),
            'paid'              => Referral::where('status', 'paid')->count(),
            'rejected'          => Referral::where('status', 'rejected')->count(),
        ]);
    }

    /** Atualiza o status de uma indicação */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', self::VALID_STATUSES),
        ]);

        $referral = Referral::findOrFail($id);

        if ($referral->status === 'rejected') {
            return response()->json(['error' => 'Não é possível alterar indicação rejeitada.'], 422);
        }

        $newStatus = $request->status;

        try {
            DB::beginTransaction();

            // Se passou para cashback_released, credita cashback bloqueado ao afiliado
            if ($newStatus === 'cashback_released' && $referral->status !== 'cashback_released') {
                $cashback = Cashback::where('user_id', $referral->referrer_id)->lockForUpdate()->first();
                if ($cashback && $referral->cashback_value > 0) {
                    $cashback->blocked_balance  += $referral->cashback_value;
                    $cashback->total_earned     += $referral->cashback_value;
                    $cashback->save();

                    CashbackHistory::create([
                        'user_id'       => $referral->referrer_id,
                        'referral_id'   => $referral->id,
                        'referral_name' => $referral->referred_name,
                        'value'         => $referral->cashback_value,
                        'is_positive'   => true,
                        'date'          => now(),
                    ]);
                }
            }

            $referral->status = $newStatus;

            if ($newStatus === 'contracted' && !$referral->contracted_at) {
                $referral->contracted_at = now();
            }
            if ($newStatus === 'paid' && !$referral->paid_at) {
                $referral->paid_at = now();
            }

            $referral->save();
            DB::commit();

            return response()->json($referral);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro interno ao atualizar status.'], 500);
        }
    }

    /** Rejeita uma indicação com motivo */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $referral = Referral::findOrFail($id);

        if (in_array($referral->status, ['paid', 'rejected'])) {
            return response()->json(['error' => 'Status inválido para rejeição.'], 422);
        }

        $referral->status          = 'rejected';
        $referral->rejection_reason = $request->reason;
        $referral->save();

        return response()->json($referral);
    }
}
