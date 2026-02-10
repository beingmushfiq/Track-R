<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stop extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Derived
        'vehicle_id',
        'device_id',
        'trip_id',
        'start_time',
        'end_time',
        'latitude',
        'longitude',
        'address',
        'duration',
        'engine_off',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'engine_off' => 'boolean',
        'duration' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
