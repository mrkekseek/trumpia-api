<?php

namespace App\Http\Middleware;

use Closure;
use DB;
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
        config(['database.connections.data' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'database' => 'api_ct',
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]]);
        DB::setDefaultConnection('data');

        $token = Token::where('token', $request->header('X-Project-Token'))->first();
        if (empty($token)) {
            return response()->error('Unauthenticated', 401);
        }
        
        config(['token.id' => $token->id]);
        config(['token.token' => $token->token]);
        config(['token.project' => $token->project]);
        config(['token.domain' => $token->domain]);
        config(['token.secure' => $token->secure]);

        return $next($request);
    }
}
