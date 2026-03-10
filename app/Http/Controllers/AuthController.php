<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cashback;
use App\Models\Referral;
use App\Rules\ValidCpf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'cpf'      => ['required', 'string', 'unique:users', new ValidCpf],
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $referredBy = null;
        if (!empty($request->referral_code)) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $referredBy = $referrer->id;
            }
        }

        // Garante unicidade do código mesmo em registros simultâneos
        do {
            $referralCode = Str::upper(Str::random(6));
        } while (User::where('referral_code', $referralCode)->exists());

        $user = User::create([
            'name'          => $request->name,
            'cpf'           => preg_replace('/[^0-9]/', '', $request->cpf),
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'referral_code' => $referralCode,
            'referral_link' => url('/register?ref=' . $referralCode),
            'referred_by'   => $referredBy,
            'role'          => 'user',
        ]);

        Cashback::create(['user_id' => $user->id]);

        // Se veio via link de indicação, cria registro automático para o afiliado
        if ($referredBy) {
            Referral::create([
                'referrer_id'   => $referredBy,
                'referred_name' => $user->name,
                'referred_cpf'  => $user->cpf,
                'plan'          => 'A definir',
                'cashback_value' => 0,
                'status'        => 'awaiting',
            ]);
        }

        $token = auth()->login($user);

        return $this->respondWithToken($token, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Credenciais inválidas.'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function forgotPassword(Request $request)
    {
        // TODO: implementar envio de e-mail via Laravel Mail (item #4 do planejamento)
        return response()->json(['message' => 'Se o e-mail existir, você receberá as instruções em breve.']);
    }

    protected function respondWithToken($token, int $status = 200)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
            'user'         => auth()->user(),
        ], $status);
    }
}
