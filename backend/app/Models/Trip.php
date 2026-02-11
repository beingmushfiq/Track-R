<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Derived
        'vehicle_id',
        'device_id',
        'start_time',
        'end_time',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'start_address',
        'end_address',
        'start_odometer',
        'end_odometer',
        'start_fuel_level',
        'end_fuel_level',
        'distance',
        'duration',
        'max_speed',
        'avg_speed',
        'fuel_consumed',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_latitude' => 'decimal:7',
        'start_longitude' => 'decimal:7',
        'end_latitude' => 'decimal:7',
        'end_longitude' => 'double',
        'start_odometer' => 'float',
        'end_odometer' => 'float',
        'start_fuel_level' => 'float',
        'end_fuel_level' => 'float',
        'distance' => 'float',
        'max_speed' => 'float',
        'avg_speed' => 'float',
        'fuel_consumed' => 'float',
        'duration' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class);
    }
}
