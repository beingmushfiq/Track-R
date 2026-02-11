<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Device;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send an alert notification to relevant users.
     */
    public function sendAlertNotification(Alert $alert): void
    {
        // Get users who should be notified (e.g., tenant admins)
        $users = $alert->device->tenant->users;

        // In a real scenario, we would filter users based on permissions or preferences
        // For now, notify all tenant users
        foreach ($users as $user) {
            try {
                $user->notify(new \App\Notifications\AlertNotification($alert));
            } catch (\Exception $e) {
                // Log error but don't stop processing other users
                \Illuminate\Support\Facades\Log::error("Failed to send alert notification to user {$user->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Send device offline notification.
     */
    public function sendDeviceOfflineNotification(Device $device): void
    {
        $users = $device->tenant->users;

        foreach ($users as $user) {
            try {
                $user->notify(new \App\Notifications\DeviceOfflineNotification($device));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send offline notification to user {$user->id}: " . $e->getMessage());
            }
        }
    }
}
