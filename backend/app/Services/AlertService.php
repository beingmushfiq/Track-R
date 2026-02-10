<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\GpsData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Evaluate rules for a device based on incoming GPS data.
     * 
     * @param Device $device
     * @param GpsData $gpsData
     */
    public function evaluate(Device $device, GpsData $gpsData): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection $rules */
        $rules = AlertRule::where('tenant_id', $device->tenant_id)
            ->where('is_active', true)
            ->with('vehicles')
            ->get();

        foreach ($rules as $rule) {
            /** @var AlertRule $rule */
            // Check scope
            if ($rule->vehicles->isNotEmpty() && !$rule->vehicles->contains('id', $device->vehicle_id)) {
                continue;
            }

            if ($this->checkCondition($rule, $gpsData)) {
                $this->triggerAlert($rule, $device, $gpsData);
            }
        }
    }

    protected function checkCondition(AlertRule $rule, GpsData $data): bool
    {
        $conditions = $rule->conditions;

        switch ($rule->type) {
            case 'overspeed':
                $speedLimit = $conditions['speed_limit'] ?? 0;
                return $data->speed > $speedLimit;

            case 'ignition_on':
                return $data->ignition === true;

            case 'ignition_off':
                return $data->ignition === false;

            default:
                return false;
        }
    }

    protected function triggerAlert(AlertRule $rule, Device $device, GpsData $data): void
    {
        $recentAlert = Alert::where('device_id', $device->id)
            ->where('alert_rule_id', $rule->id)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->exists();

        if ($recentAlert) {
            return;
        }

        $alert = Alert::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'vehicle_id' => $device->vehicle_id,
            'alert_rule_id' => $rule->id,
            'type' => $rule->type,
            'severity' => 'high', // Default, should take from rule
            'title' => $rule->name,
            'message' => "Rule '{$rule->name}' triggered at {$data->gps_time}",
            'latitude' => $data->latitude,
            'longitude' => $data->longitude,
            'data' => [
                'speed' => $data->speed,
                'condition' => $rule->conditions
            ],
            'is_read' => false
        ]);

        Log::info("Alert triggered: {$alert->id} for Device: {$device->imei}");
    }
}
