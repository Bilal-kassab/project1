<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ApproveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8'
        ]);

        $user=User::where('email',$request->email)->first();
        if(!$user || !Hash::check($request['password'],$user->password)){
            return response()->json([
                'message' =>trans('auth.failed')
            ],401);
        }
        if(!$user->is_approved){
            return response()->json([
                'message'=>trans('auth.accept-admin')
            ],200);
        }

        return $next($request);
    }
}
