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
        'gps_valid',
        'address',
        'raw_data',
        'gps_time',
        'server_time',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'altitude' => 'decimal:2',
        'speed' => 'decimal:2',
        'odometer' => 'decimal:2',
        'fuel_level' => 'decimal:2',
        'battery_voltage' => 'decimal:2',
        'ignition' => 'boolean',
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
