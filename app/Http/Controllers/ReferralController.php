<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referral;
use App\Rules\ValidCpf;

class ReferralController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'referredName'  => 'required|string|max:255',
            'referredCpf'   => ['required', 'string', 'max:14', new ValidCpf],
            'referredPhone' => 'nullable|string|max:20',
            'plan'          => 'required|string|max:100',
            'cashbackValue' => 'required|numeric|min:0',
        ]);

        $cpf = preg_replace('/[^0-9]/', '', $request->referredCpf);

        // Impede que o mesmo CPF seja indicado mais de uma vez (exceto rejeitados)
        $alreadyExists = Referral::where('referred_cpf', $cpf)
            ->whereNotIn('status', ['rejected'])
            ->exists();

        if ($alreadyExists) {
            return response()->json(
                ['error' => 'Este CPF já possui uma indicação ativa no sistema.'],
                422
            );
        }

        $referral = Referral::create([
            'referrer_id'    => auth()->id(),
            'referred_name'  => $request->referredName,
            'referred_cpf'   => $cpf,
            'referred_phone' => $request->referredPhone,
            'plan'           => $request->plan,
            'cashback_value' => $request->cashbackValue,
            'status'         => 'awaiting',
        ]);

        return response()->json($referral, 201);
    }

    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $query   = Referral::where('referrer_id', auth()->id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }

    public function stats()
    {
        $base = Referral::where('referrer_id', auth()->id());

        return response()->json([
            'total'    => (clone $base)->count(),
            'waiting'  => (clone $base)->where('status', 'awaiting')->count(),
            'active'   => (clone $base)->where('status', 'contracted')->count(),
            'released' => (clone $base)->where('status', 'cashback_released')->count(),
        ]);
    }
}
