<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\GpsData;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ProcessGpsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data; // Raw array from Redis

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
        try {
            $imei = $this->data['imei'] ?? null;

            if (!$imei) {
                Log::warning('ProcessGpsData: Missing IMEI in payload', $this->data);
                return;
            }

            // Find device (without global scopes to find cross-tenant if needed, 
            // though usually we want to respect tenancy. For now, let's assume valid IMEI = valid device)
            // We use withoutGlobalScopes because the worker context might not have tenant_id set
            $device = Device::withoutGlobalScopes()
                ->where('imei', $imei)
                ->first();

            if (!$device) {
                Log::info("ProcessGpsData: Unknown device IMEI: {$imei}");
                return;
            }

            // Update Device Status
            $device->update([
                'last_communication' => Carbon::parse($this->data['serverTime']),
                'is_online' => true, // It just sent data
            ]);

            // Create GPS Data Record
            $gpsData = new GpsData();
            // Manually set tenant_id because we are running in a bg job potentially without session
            $gpsData->tenant_id = $device->tenant_id;
            $gpsData->device_id = $device->id;
            $gpsData->vehicle_id = $device->vehicle_id;

            $gpsData->latitude = $this->data['latitude'];
            $gpsData->longitude = $this->data['longitude'];
            $gpsData->altitude = $this->data['altitude'] ?? null;
            $gpsData->speed = $this->data['speed'] ?? 0;
            $gpsData->heading = $this->data['heading'] ?? 0;
            $gpsData->satellites = $this->data['satellites'] ?? null;
            $gpsData->hdop = $this->data['hdop'] ?? null;
            $gpsData->odometer = $this->data['odometer'] ?? null; // Should ideally calculate if missing
            $gpsData->fuel_level = $this->data['fuel_level'] ?? null;
            $gpsData->battery_voltage = $this->data['battery_voltage'] ?? null;
            $gpsData->gps_valid = $this->data['gps_valid'] ?? true;
            $gpsData->ignition = $this->data['ignition'] ?? null;

            // Timestamps
            $gpsData->gps_time = isset($this->data['timestamp'])
                ? Carbon::createFromTimestampMs($this->data['timestamp'])
                : Carbon::now();

            $gpsData->server_time = Carbon::parse($this->data['serverTime']);

            $gpsData->raw_data = $this->data; // Store full payload for debugging

            $gpsData->save();

            // Check Geofences (Placeholder)
            // $this->checkGeofences($device, $gpsData);

            // Check Alerts (Placeholder)
            // $this->checkAlerts($device, $gpsData);

        } catch (\Exception $e) {
            Log::error('ProcessGpsData: Error processing job', [
                'error' => $e->getMessage(),
                'data' => $this->data
            ]);
            // Don't fail the job hard so it doesn't retry infinitely on bad data? 
            // Or maybe fail it to DLQ. For now, just log.
        }
    }
}
