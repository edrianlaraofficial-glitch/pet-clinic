<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedEmail
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.require_email_verification') || $request->user()?->hasVerifiedEmail()) {
            return $next($request);
        }

        return redirect()->route('verification.notice');
    }
}
