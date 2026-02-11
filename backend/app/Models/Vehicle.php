<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_type_id',
        'vehicle_group_id',
        'name',
        'registration_number',
        'vin',
        'make',
        'model',
        'year',
        'color',
        'fuel_capacity',
        'fuel_consumption',
        'driver_name',
        'driver_phone',
        'photo',
        'custom_fields',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'fuel_capacity' => 'decimal:2',
        'fuel_consumption' => 'decimal:2',
        'custom_fields' => 'array',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(VehicleGroup::class, 'vehicle_group_id');
    }

    public function trips(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function device(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Device::class)->latestOfMany();
    }
}
