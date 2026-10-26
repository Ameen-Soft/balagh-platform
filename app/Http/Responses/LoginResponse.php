<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        $user = $request->user();

        // Clear any stale intended URLs that might point to wrong or unauthorized portals
        session()->forget('url.intended');

        if ($user) {
            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasRole('Ministry Admin')) {
                return redirect()->route('ministry.dashboard');
            }
        }

        return redirect()->route('dashboard');
    }
}
