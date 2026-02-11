<?php

namespace App\Policies;

use App\Models\PanicEvent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PanicEventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any panic events.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view panic events');
    }

    /**
     * Determine whether the user can view the panic event.
     */
    public function view(User $user, PanicEvent $panicEvent): bool
    {
        // User must be in the same tenant
        if ($user->tenant_id !== $panicEvent->tenant_id) {
            return false;
        }

        return $user->hasPermissionTo('view panic events');
    }

    /**
     * Determine whether the user can create panic events.
     */
    public function create(User $user): bool
    {
        // Typically panic events are created by the system, not users
        // But drivers or vehicle operators might trigger them
        return $user->hasPermissionTo('create panic events');
    }

    /**
     * Determine whether the user can update the panic event.
     */
    public function update(User $user, PanicEvent $panicEvent): bool
    {
        // User must be in the same tenant
        if ($user->tenant_id !== $panicEvent->tenant_id) {
            return false;
        }

        // Only allow resolving panic events, not editing other fields
        return $user->hasPermissionTo('resolve panic events');
    }

    /**
     * Determine whether the user can delete the panic event.
     */
    public function delete(User $user, PanicEvent $panicEvent): bool
    {
        // User must be in the same tenant
        if ($user->tenant_id !== $panicEvent->tenant_id) {
            return false;
        }

        return $user->hasPermissionTo('delete panic events');
    }
}
