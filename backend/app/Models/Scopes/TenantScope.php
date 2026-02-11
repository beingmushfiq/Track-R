<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (session()->has('tenant_id')) {
            $builder->where('tenant_id', session()->get('tenant_id'));
        } elseif (auth()->check()) {
            // If user is a super admin (no tenant_id or special role), they might see all?
            // For now, let's assume strict tenancy based on user's tenant_id if set.
            $user = auth()->user();
            if ($user && $user->tenant_id) {
                $builder->where($model->qualifyColumn('tenant_id'), $user->tenant_id);
            }
        }
    }
}
