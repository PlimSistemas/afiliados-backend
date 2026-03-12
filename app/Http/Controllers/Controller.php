<?php

namespace App\Http\Controllers;

use App\Models\User;

abstract class Controller
{

    protected function authUser(): User
    {
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth();

        /** @var User $user */
        $user = $auth->user();

        return $user;
    }

    protected function auth()
    {
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth();
        return $auth;
    }
}
