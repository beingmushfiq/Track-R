<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSubscription extends Model
{
    use Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'report_type',
        'delivery_method',
        'delivery_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'delivery_time' => 'datetime:H:i:s', // Or keep as string if better
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
