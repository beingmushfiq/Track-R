<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeofenceEvent extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Derived
        'geofence_id',
        'vehicle_id',
        'device_id',
        'event_type', // enter, exit
        'event_time',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function geofence(): BelongsTo
    {
        return $this->belongsTo(Geofence::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
