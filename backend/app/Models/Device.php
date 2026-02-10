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
        'sim_number',
        'sim_provider',
        'installation_date',
        'expiry_date',
        'configuration',
        'status',
        'is_online',
        'last_communication',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'expiry_date' => 'date',
        'configuration' => 'array',
        'is_online' => 'boolean',
        'last_communication' => 'datetime',
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
