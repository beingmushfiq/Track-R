<?php

namespace App\Services;

use App\Models\ReportSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ReportSubscriptionService
{
    public function getUserSubscriptions(User $user): Collection
    {
        return ReportSubscription::where('user_id', $user->id)->get();
    }

    public function subscribe(User $user, array $data): ReportSubscription
    {
        return ReportSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'report_type' => $data['report_type'] ?? 'daily',
            ],
            [
                'tenant_id' => $user->tenant_id,
                'delivery_method' => $data['delivery_method'] ?? 'email',
                'delivery_time' => $data['delivery_time'] ?? '08:00:00',
                'is_active' => true,
            ]
        );
    }

    public function unsubscribe(User $user, int $subscriptionId): bool
    {
        $subscription = ReportSubscription::where('user_id', $user->id)
            ->where('id', $subscriptionId)
            ->firstOrFail();

        return $subscription->delete();
    }
    
    public function updateSubscription(User $user, int $subscriptionId, array $data): ReportSubscription
    {
        $subscription = ReportSubscription::where('user_id', $user->id)
            ->where('id', $subscriptionId)
            ->firstOrFail();
            
        $subscription->update($data);
        
        return $subscription;
    }
}
