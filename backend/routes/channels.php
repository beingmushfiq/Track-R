<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tenant.{tenantId}.tracking', function ($user, $tenantId) {
    return (int) $user->tenant_id === (int) $tenantId;
});

Broadcast::channel('tenant.{tenantId}.alerts', function ($user, $tenantId) {
    return (int) $user->tenant_id === (int) $tenantId;
});

Broadcast::channel('tenant.{tenantId}.geofences', function ($user, $tenantId) {
    return (int) $user->tenant_id === (int) $tenantId;
});

Broadcast::channel('tenant.{tenantId}.devices', function ($user, $tenantId) {
    return (int) $user->tenant_id === (int) $tenantId;
});
