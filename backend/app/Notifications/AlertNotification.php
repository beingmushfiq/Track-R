<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\TwilioChannel;

use App\Channels\WhatsAppChannel;

class AlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $alert;

    /**
     * Create a new notification instance.
     */
    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', TwilioChannel::class, WhatsAppChannel::class];
    }

    public function toTwilio($notifiable)
    {
        $vehicleName = $this->alert->vehicle ? $this->alert->vehicle->name : 'Vehicle';
        return "Track-R Alert: {$this->alert->title} for {$vehicleName}. Msg: {$this->alert->message}";
    }

    public function toWhatsApp($notifiable)
    {
        $vehicleName = $this->alert->vehicle ? $this->alert->vehicle->name : 'Vehicle';
        return "🚨 *Track-R Alert*\n\n" .
               "Vehicle: *{$vehicleName}*\n" .
               "Type: {$this->alert->type}\n" .
               "Message: {$this->alert->message}\n" .
               "Time: {$this->alert->created_at->format('Y-m-d H:i:s')}";
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $vehicleName = $this->alert->vehicle ? $this->alert->vehicle->name : 'Unknown Vehicle';
        
        return (new MailMessage)
                    ->subject("Alert: {$this->alert->title} - {$vehicleName}")
                    ->line("An alert was triggered for vehicle: {$vehicleName}")
                    ->line("Type: {$this->alert->type}")
                    ->line("Message: {$this->alert->message}")
                    ->line("Time: {$this->alert->created_at->format('Y-m-d H:i:s')}")
                    ->action('View Alert', url("/dashboard/alerts/{$this->alert->id}"))
                    ->line('Thank you for using Track-R!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'vehicle_id' => $this->alert->vehicle_id,
            'type' => $this->alert->type,
            'title' => $this->alert->title,
            'message' => $this->alert->message,
            'created_at' => $this->alert->created_at->toIso8601String(),
        ];
    }
}
