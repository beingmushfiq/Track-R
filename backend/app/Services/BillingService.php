<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Subscribe a tenant to a plan.
     */
    public function subscribe(Tenant $tenant, Plan $plan, ?string $paymentMethod = 'manual'): Subscription
    {
        return DB::transaction(function () use ($tenant, $plan, $paymentMethod) {
            // Cancel any active subscriptions
            $tenant->subscriptions()->where('status', 'active')->update([
                'status' => 'cancelled',
                'ends_at' => Carbon::now(),
            ]);

            $startDate = Carbon::now();
            $endDate = $this->calculateEndDate($startDate, $plan->billing_cycle);

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'starts_at' => $startDate,
                'ends_at' => $endDate,
                'status' => 'active',
                'payment_method' => $paymentMethod,
            ]);

            // Generate initial invoice
            $this->generateInvoice($subscription);

            return $subscription;
        });
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription): bool
    {
        if ($subscription->status !== 'active') {
            return false;
        }

        $subscription->update([
            'status' => 'cancelled',
            'ends_at' => Carbon::now(),
        ]);

        return true;
    }

    /**
     * Generate an invoice for a subscription.
     */
    public function generateInvoice(Subscription $subscription): Invoice
    {
        $plan = $subscription->plan;
        $amount = $plan->price;
        $dueDate = Carbon::now()->addDays(7);

        return Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-' . strtoupper(Str::random(10)), // Simple generation for now
            'amount' => $amount,
            'total' => $amount, // Add tax calculation logic if needed
            'status' => 'unpaid',
            'due_date' => $dueDate,
            'currency' => 'USD',
        ]);
    }

    /**
     * Calculate end date based on billing cycle.
     */
    protected function calculateEndDate(Carbon $startDate, string $billingCycle): Carbon
    {
        return match ($billingCycle) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'yearly' => $startDate->copy()->addYear(),
            default => $startDate->copy()->addMonth(),
        };
    }
}
