<?php

namespace App\Http\Controllers;

use App\Jobs\SendStaticTripReminder;
use App\Models\Booking;
use Illuminate\Http\Request;

class SendTripReminderController extends Controller
{
    public function sendStaticTripReminder()
    {
        $dateTomorrow = now()->addDay()->format('Y-m-d');
        $trips = Booking::whereHas('bookings.user')->whereDate('start_date', $dateTomorrow)->where('type','static')->with('bookings.user')->get();
        $arr=[];
        foreach($trips as $trip){
            foreach($trip['bookings'] as $users){
                $arr[]=$users['user'];
            }
                //  $arr[]=$trip['bookings'];
        }

        $message=[
            'title'=>'Trip Reminder',
            'body'=>"Dear ".auth()->user()->name.", you have a journey starting tomorrow. Be prepared!.",
        ];
        dispatch(new SendStaticTripReminder($arr,$message));
        // event(new PushWebNotification($arr,$message));
     return "done";
    }
}
