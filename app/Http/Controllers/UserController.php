<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me()
    {
        return $this->userResponse();
    }

    public function update(Request $request)
    {
        $request->validate([
            'pix_key' => 'nullable|string|max:255',
            'name'    => 'nullable|string|max:255',
        ]);

        $user = $this->authUser();

        $user->fill($request->only(['pix_key', 'name']));
        $user->save();

        return $this->userResponse();
    }

    private function userResponse(){
        $user = $this->auth()->user();
        $frontendUrl = env('FRONTEND_URL');

        if ($user && isset($user->referral_link)) {
            $user->referral_link = $frontendUrl . $user->referral_link;
        }
        return response()->json($user);
    }
}
