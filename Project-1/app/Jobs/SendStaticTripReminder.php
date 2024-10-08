<?php

namespace App\Jobs;

use App\Helpers\PushNotificationWeb;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendStaticTripReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $users,$message;
    public function __construct($users,$message)
    {
        $this->users=$users;
        $this->message=$message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users=$this->users;
        $message=$this->message;
        foreach($users as $user){
            $message=[
                'title'=>'Trip Reminder',
                'body'=>"Dear".$user->name .", you have a journey starting tomorrow. Be prepared!.",
            ];
            Notification::create([
                'user_id'=>$user->id,
                'title'=>$message['title'],
                'body'=>$message['body'],
            ]);
            PushNotificationWeb::sendNotification($message,$user->fcm_token);
        }
    }
}
