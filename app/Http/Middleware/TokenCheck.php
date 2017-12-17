<?php

namespace App\Http\Middleware;

use Closure;
use App\Token;

class TokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = Token::where('token', $request->header('X-Project-Token'))->first();
        if (empty($token)) {
            return response()->error('Unauthenticated', 401);
        }
        
        config(['token.token' => $token->token]);
        config(['token.project' => $token->project]);
        config(['token.domain' => $token->domain]);
        config(['token.secure' => $token->secure]);

        return $next($request);
    }
}
