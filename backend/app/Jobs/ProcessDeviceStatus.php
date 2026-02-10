<?php

namespace App\Jobs;

use App\Models\Device;
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
            return;
        }

        $device = Device::withoutGlobalScopes()
            ->where('imei', $imei)
            ->first();

        if ($device) {
            $device->update([
                'is_online' => ($status === 'online'),
                'last_communication' => Carbon::parse($this->data['timestamp']),
            ]);

            Log::info("Device {$imei} is now {$status}");
        }
    }
}
