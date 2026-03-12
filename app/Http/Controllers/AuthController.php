<?php

namespace App\Http\Controllers;

use App\Models\Cashback;
use App\Models\PasswordResetTokens;
use App\Models\Referral;
use App\Models\User;
use App\Rules\ValidCpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'cpf'      => ['required', 'string', 'unique:users', new ValidCpf],
            'email'    => 'required|string|email|unique:users',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
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
            'referral_link' => '/cadastro?ref=' . $referralCode,
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

        $token = $this->auth()->login($user);

        return $this->respondWithToken($token, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = $this->auth()->attempt($credentials)) {
            return response()->json(['error' => 'Email ou senha inválidas.'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function refresh()
    {
        return $this->respondWithToken($this->auth()->refresh());
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        // Criar ou atualizar token de redefinição de senha
        if ($user) {
            $code = bin2hex(random_bytes(16));
            $name = ucfirst(strtolower(explode(' ', trim(($user->name ?? '')))[0])); //Primeiro nome e primeira letra maiuscula

            PasswordResetTokens::updateOrCreate(
                ['email' => $request->email],
                [
                    'token' => bcrypt($code),
                    'created_at' => now()
                ]
            );

            Mail::send('emails.password-reset', [
                'user' => $name,
                'resetUrl' => env('FRONTEND_URL') . '/redefinir-senha/' . $code
            ], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Recuperação de Senha - Plim Afiliados');
            });
        }

        // TODO: implementar envio de e-mail via Laravel Mail (item #4 do planejamento)
        return response()->json(['message' => 'Se o e-mail existir, você receberá as instruções em breve.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $record = PasswordResetTokens::where('email', $request->email)->first();
        if (!$record || !Hash::check($request->token, $record->token) || $record->created_at->addMinutes(60)->isPast()) {
            return response()->json(['error' => 'Token inválido ou expirado.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Exclui o token após uso
        $record->delete();

        return response()->json(['message' => 'Senha redefinida com sucesso.']);
    }

    protected function respondWithToken($token, int $status = 200)
    {

        $user = $this->auth()->user();
        $frontendUrl = env('FRONTEND_URL');

        if ($user && isset($user->referral_link)) {
            $user->referral_link = $frontendUrl . $user->referral_link;
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $this->auth()->factory()->getTTL() * 60,
            'user'         => $user,
        ], $status);
    }
}
