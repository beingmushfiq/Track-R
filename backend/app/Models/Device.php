<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'device_model_id',
        'imei',
        'unique_id',
        'sim_number',
        'sim_provider',
        'phone_number',
        'installation_date',
        'expiry_date',
        'configuration',
        'status',
        'is_online',
        'last_communication',
        'last_seen_at',
        'engine_locked',
        'last_command_at',
        'last_command_type',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'expiry_date' => 'date',
        'configuration' => 'array',
        'is_online' => 'boolean',
        'last_communication' => 'datetime',
        'last_seen_at' => 'datetime',
        'engine_locked' => 'boolean',
        'last_command_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'device_model_id');
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }
}
