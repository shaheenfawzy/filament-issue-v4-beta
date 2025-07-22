<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginAsFirstUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            auth()->loginUsingId(1);

            return redirect()->to(filament()->getUrl());
        }

        return $next($request);
    }
}
