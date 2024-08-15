<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PushNotificationWeb
{
    public static function sendNotification($message,$fcm_token)
    {
        try{
            $apiUrl = 'https://fcm.googleapis.com/v1/projects/test-49b2e/messages:send';
            $access_token = Cache::remember('access_token', now()->addHour(), function () use ($apiUrl) {
                $credentialsFilePath = storage_path('app/fcm.json');
                // dd($credentialsFilePath);
                $client = new \Google_Client();
                $client->setAuthConfig($credentialsFilePath);
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                // dd($client);
                $client->fetchAccessTokenWithAssertion();
                $token = $client->getAccessToken();
                return $token['access_token'];
            });

            $message = [
                "message" => [
                    "token" => $fcm_token,
                    "notification" => [
                        "title" => $message['title'],
                        "body" => $message['body'],
                    ]
                ]
            ];

            $response = Http::withHeader('Authorization', "Bearer $access_token")->post($apiUrl, $message);
            // return "sent";

        }catch(Exception $ex){
            return response()->json([
                'message'=>$ex->getMessage(),
            ]);
        }
    }
}
