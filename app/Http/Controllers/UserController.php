<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me()
    {
        return response()->json(auth()->user());
    }

    public function update(Request $request)
    {
        $request->validate([
            'pix_key' => 'nullable|string|max:255',
            'name'    => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $user->fill($request->only(['pix_key', 'name']));
        $user->save();

        return response()->json($user);
    }
}
