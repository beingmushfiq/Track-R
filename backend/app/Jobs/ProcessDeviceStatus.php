<?php

namespace App\Jobs;

use App\Models\Device;
use App\Events\DeviceStatusChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ProcessDeviceStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $imei = $this->data['imei'] ?? null;
        $status = $this->data['status'] ?? null; // 'online' or 'offline'

        if (!$imei || !$status) {
            Log::warning('ProcessDeviceStatus: Missing IMEI or status data', ['data' => $this->data]);
            return;
        }

        try {
            $device = Device::withoutGlobalScopes()
                ->where('imei', $imei)
                ->first();

            if ($device) {
                $isOnline = ($status === 'online');
                $lastCommunication = Carbon::parse($this->data['timestamp']);

                $device->is_online = $isOnline;
                $device->last_communication = $lastCommunication;
                $device->save();

                // Broadcast device status change
                event(new DeviceStatusChanged($device));

                Log::info("Device {$device->imei} status changed to " . ($isOnline ? 'online' : 'offline'));

                if (!$isOnline) {
                    try {
                        /** @var \App\Services\NotificationService $notificationService */
                        $notificationService = app(\App\Services\NotificationService::class);
                        $notificationService->sendDeviceOfflineNotification($device);
                    } catch (\Exception $e) {
                        Log::error("Failed to send offline notifications: " . $e->getMessage());
                    }
                }
            } else {
                Log::warning('ProcessDeviceStatus: Device not found', ['imei' => $imei]);
            }
        } catch (\Exception $e) {
            Log::error('ProcessDeviceStatus: Error processing job', [
                'error' => $e->getMessage(),
                'imei' => $imei,
                'data' => $this->data,
            ]);
        }
    }
}
