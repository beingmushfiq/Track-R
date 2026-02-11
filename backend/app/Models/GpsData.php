<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsData extends Model
{
    // GPS Data is high-volume and often accessed without tenant context for analytics, 
    // but usually we want tenant scoping.
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Note: Derived from device->tenant_id usually
        'device_id',
        'vehicle_id',
        'latitude',
        'longitude',
        'altitude',
        'speed',
        'heading',
        'satellites',
        'hdop',
        'odometer',
        'fuel_level',
        'battery_voltage',
        'gsm_signal',
        'ignition',
        'panic_button',
        'rpm',
        'gps_valid',
        'address',
        'raw_data',
        'gps_time',
        'server_time',
        'coolant_temp',
        'engine_load',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'altitude' => 'float',
        'speed' => 'float',
        'odometer' => 'float',
        'fuel_level' => 'float',
        'battery_voltage' => 'float',
        'ignition' => 'boolean',
        'panic_button' => 'boolean',
        'rpm' => 'integer',
        'gps_valid' => 'boolean',
        'raw_data' => 'array',
        'gps_time' => 'datetime',
        'server_time' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
