<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProperty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user=auth()->user();
        if($request->user()->hasAnyRole(['Super Admin','Airport admin']) || (auth()->id() != $request->user_id) )
        {
            return response()->json([
                'message'=>'You do not have the permission'
            ],200);
        }
        return $next($request);
    }
}
