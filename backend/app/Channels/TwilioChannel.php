<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toTwilio')) {
            return;
        }

        $message = $notification->toTwilio($notifiable);
        
        $to = $notifiable->routeNotificationFor('twilio');

        if (!$to && isset($notifiable->phone_number)) {
             $to = $notifiable->phone_number;
        }
        
        if (!$to) {
            return;
        }

        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            if (!$sid || !$token || !$from) {
                // Log only once per request/process to avoid flooding logs if config is missing
                // For now, just return
                return;
            }

            $client = new Client($sid, $token);

            $client->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);
            
            Log::info("SMS sent to {$to}: {$message}");

        } catch (\Exception $e) {
            Log::error("Twilio SMS Error: " . $e->getMessage());
        }
    }
}
