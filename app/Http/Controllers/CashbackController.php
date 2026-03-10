<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cashback;
use App\Models\CashbackHistory;

class CashbackController extends Controller
{
    public function balance()
    {
        $cashback = Cashback::where('user_id', auth()->id())->first();
        if (!$cashback) {
            $cashback = Cashback::create(['user_id' => auth()->id()]);
        }
        return response()->json([
            'available_balance' => $cashback->available_balance,
            'blocked_balance' => $cashback->blocked_balance,
            'total_earned' => $cashback->total_earned,
        ]);
    }

    public function history(Request $request)
    {
        $limit = $request->get('limit', 50);
        $history = CashbackHistory::where('user_id', auth()->id())
            ->orderByDesc('date')
            ->limit($limit)
            ->get();
        return response()->json($history);
    }
}
