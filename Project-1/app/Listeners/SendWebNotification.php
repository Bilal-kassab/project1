<?php

namespace App\Listeners;

use App\Events\PushWebNotification;
use App\Helpers\PushNotificationWeb;
use App\Jobs\SendWebNotification as JobsSendWebNotification;
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
        //  PushNotificationWeb::sendNotification($message,$users);
         dispatch(new JobsSendWebNotification($users,$message));
    }
}
