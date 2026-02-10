<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'type',
        'parameters',
        'is_scheduled',
        'schedule_frequency',
        'recipients',
        'last_run_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_scheduled' => 'boolean',
        'recipients' => 'array',
        'last_run_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
