<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = $request->user();
        
        return redirect()->intended(
            $user->isSuperAdmin()
                ? route('admin.dashboard')
                : ($user->isAdmin()
                    ? route('admin.dashboard')
                    : route('landing'))
        );
    }
}