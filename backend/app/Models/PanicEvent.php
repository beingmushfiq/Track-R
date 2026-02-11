<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanicEvent extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'device_id',
        'vehicle_id',
        'latitude',
        'longitude',
        'triggered_at',
        'resolved_at',
        'notes',
        'resolved_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the device that triggered the panic.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the vehicle associated with the panic event.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Check if the panic event is resolved.
     */
    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    /**
     * Resolve the panic event.
     */
    public function resolve(string $resolvedBy, string $notes = null): void
    {
        $this->update([
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'notes' => $notes ?? $this->notes,
        ]);
    }
}
