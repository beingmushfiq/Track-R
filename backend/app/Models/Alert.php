<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'device_id',
        'type',
        'severity',
        'title',
        'message',
        'data',
        'latitude',
        'longitude',
        'address',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
