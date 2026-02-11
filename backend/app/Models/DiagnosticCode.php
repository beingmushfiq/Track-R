<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosticCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'device_id',
        'vehicle_id',
        'code',
        'description',
        'severity',
        'detected_at',
        'cleared_at',
        'cleared_by',
        'notes',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'cleared_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the diagnostic code.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the device that reported the code.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the vehicle associated with the code.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who cleared the code.
     */
    public function clearedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    /**
     * Check if the code is active (not cleared).
     */
    public function isActive(): bool
    {
        return is_null($this->cleared_at);
    }

    /**
     * Mark the code as cleared.
     */
    public function clear(int $userId, ?string $notes = null): bool
    {
        $this->cleared_at = now();
        $this->cleared_by = $userId;
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Scope to get only active codes.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('cleared_at');
    }

    /**
     * Scope to get codes by severity.
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }
}
