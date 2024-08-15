<?php

namespace App\Listeners;

use App\Events\PushWebNotification;
use App\Jobs\SendWebNotification as JobsSendWebNotification;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWebNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PushWebNotification $event): void
    {
         $users=$event->users;
         $message=$event->message;
        //  throw new \Exception('jjj');
         foreach($users as $user){
            if($user->fcm_token){
                $notification=new Notification();
                $notification->user_id=$user->id;
                $notification->title=$message['title'];
                $notification->body=$message['body'];
                $notification->save();
            }
        }
         dispatch(new JobsSendWebNotification($users,$message));
    }
}
