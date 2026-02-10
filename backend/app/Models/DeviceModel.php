<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceModel extends Model
{
    protected $fillable = [
        'manufacturer',
        'model',
        'protocol',
        'default_port',
        'features',
        'configuration',
    ];

    protected $casts = [
        'features' => 'array',
        'configuration' => 'array',
    ];

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
