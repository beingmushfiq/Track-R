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
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'start_address',
        'end_address',
        'distance',
        'duration',
        'max_speed',
        'avg_speed',
        'fuel_consumed',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_lat' => 'decimal:7',
        'start_lng' => 'decimal:7',
        'end_lat' => 'decimal:7',
        'end_lng' => 'decimal:7',
        'distance' => 'decimal:2',
        'max_speed' => 'decimal:2',
        'avg_speed' => 'decimal:2',
        'fuel_consumed' => 'decimal:2',
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
