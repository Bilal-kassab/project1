<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bank\RechargeRequest;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function myAccount():JsonResponse
    {
        $account=Bank::where('email',auth()->user()->email)->first();
        return response()->json([
            'data'=>$account
        ],200);
    }

    public function recharge(RechargeRequest $request):JsonResponse
    {
        $account=Bank::where('email',$request->email)->firstOrFail();
        $account['money']=$account['money']+$request->money;
        $account->save();
        return response()->json([
            'message'=>'Money added successfully',
        ],200);
    }
}
