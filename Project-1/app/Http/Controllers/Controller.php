<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Stripe\Stripe;
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // public function payment()
    // {
    //     $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    //     $response=$stripe->checkout->sessions->create([
    //     'line_items' => [
    //         [
    //             'price_data' => [
    //                 'currency' => 'usd',
    //                 'product_data' => [
    //                         'name' => 'Trip name',
    //                 ],
    //                 //trip price
    //                 'unit_amount' => 2000,
    //             ],
    //             'quantity' => 1,
    //         ]
    //     ],
    //     'mode' => 'payment',
    //     // 'success_url' => route('success').'?session_id={CHECKOUT_SESSION_ID}',
    //     'success_url' => route('success'),
    //     'cancel_url' => route('cancel'),
    //     ]);

    //     if(isset($response->id)&& $response->id != ''){
    //         return redirect($response->url);
    //     }else{
    //         return redirect()->route('cancel');
    //     }

    // }

    // public function success(){
    //         // if(isset($request->session_id)){
    //         //     $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    //         //     $response=$stripe->checkout->sessions->retrieve($request->session_id);

    //         //     return response()->json([
    //         //         'data'=>$response
    //         //     ]);

    //         // }else{
    //         //     return redirect()->route('cancel');
    //         // }
    //         return response()->json([
    //             'message'=>'Enjoy Trip'
    //         ],200);
    // }

    // public function cancel()
    // {
    //     return response()->json([
    //         'message'=>'failed plz try again'
    //     ],422);
    // }
}
