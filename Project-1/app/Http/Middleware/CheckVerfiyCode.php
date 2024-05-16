<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVerfiyCode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        $user=auth()->user();
        if ( is_null($user['email_verified_at'])) {
            return response()->json([
                'message'=>'verfiy ur email plz'
            ],200);

        }
        return $next($request);
    }
}
