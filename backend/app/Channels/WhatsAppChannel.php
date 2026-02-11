<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);
        
        $to = $notifiable->routeNotificationFor('whatsapp');

        if (!$to && isset($notifiable->phone)) {
             $to = $notifiable->phone;
        }
        
        if (!$to) {
            return;
        }

        // Ensure number has whatsapp: prefix
        if (!str_starts_with($to, 'whatsapp:')) {
            $to = 'whatsapp:' . $to;
        }

        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.whatsapp_from'); // Specific WhatsApp sender

            if (!$sid || !$token || !$from) {
                return;
            }

            $client = new Client($sid, $token);

            $client->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);
            
            Log::info("WhatsApp sent to {$to}: {$message}");

        } catch (\Exception $e) {
            Log::error("Twilio WhatsApp Error: " . $e->getMessage());
        }
    }
}
