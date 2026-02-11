<?php

namespace App\Notifications;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeviceOfflineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $device;

    /**
     * Create a new notification instance.
     */
    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $vehicleName = $this->device->vehicle ? $this->device->vehicle->name : 'Unassigned Device';
        
        return (new MailMessage)
                    ->error() // Red styling for offline
                    ->subject("Device Offline: {$vehicleName}")
                    ->line("Device associated with vehicle '{$vehicleName}' has gone offline.")
                    ->line("Last Communication: {$this->device->last_communication->format('Y-m-d H:i:s')}")
                    ->action('View Device', url("/dashboard/devices/{$this->device->id}"))
                    ->line('Please check the device status.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'device_id' => $this->device->id,
            'vehicle_id' => $this->device->vehicle_id,
            'type' => 'device_offline',
            'title' => 'Device Offline',
            'message' => "Device {$this->device->imei} went offline at {$this->device->last_communication}",
            'created_at' => now()->toIso8601String(),
        ];
    }
}
