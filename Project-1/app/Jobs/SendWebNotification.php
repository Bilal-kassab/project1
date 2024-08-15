<?php

namespace App\Jobs;

use App\Helpers\PushNotificationWeb;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWebNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $users,$message;
    public function __construct($users,$message)
    {
        $this->users = $users;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users=$this->users;
        $message=$this->message;

        //  PushNotificationWeb::sendNotification($message,$users);
        foreach($users as $user){
            // $notification=new Notification();
            // $notification->user_id=2;
            // $notification->title="bb";
            // $notification->body='hjk';
            // // $notification->user_id=$user->id;
            // // $notification->title=$message['title'];
            // // $notification->body=$message['body'];
            // $notification->save();
            // // Notification::create([
            // //     'user_id'=>$user->id,
            // //     'title'=>$message['title'],
            // //     'body'=>$message['body'],
            // // ]);
            PushNotificationWeb::sendNotification($message,$user->fcm_token);
        }
    }
}
