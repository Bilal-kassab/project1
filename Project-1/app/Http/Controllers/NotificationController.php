<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():JsonResponse
    {
        $notifications=Notification::where('user_id',auth()->id())->get();
        return response()->json([
            'data'=>$notifications
        ],200);
    }

    public function getNotes():JsonResponse
    {
        $notes=Booking::where('user_id',auth()->id())->whereNotNull('trip_note')->select('id','trip_name','trip_note','type')->get();
        return response()->json([
            'data'=>$notes
        ],200);
    }

}
