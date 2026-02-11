<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BillingController extends Controller
{
    protected BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * List all available plans.
     */
    public function plans(): JsonResponse
    {
        $plans = Plan::where('is_active', true)->get();
        return response()->json(['data' => $plans]);
    }

    /**
     * Get current subscription details.
     */
    public function subscription(Request $request): JsonResponse
    {
        $subscription = $request->user()->tenant->subscription()->with('plan')->first();

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found.'], 404);
        }

        return response()->json(['data' => $subscription]);
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'nullable|string',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $subscription = $this->billingService->subscribe(
            $request->user()->tenant,
            $plan,
            $request->payment_method
        );

        return response()->json([
            'message' => 'Subscription created successfully.',
            'data' => $subscription
        ], 201);
    }

    /**
     * Cancel current subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $subscription = $request->user()->tenant->subscription;

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found.'], 404);
        }

        if ($this->billingService->cancel($subscription)) {
            return response()->json(['message' => 'Subscription cancelled successfully.']);
        }

        return response()->json(['message' => 'Unable to cancel subscription.'], 400);
    }

    /**
     * List invoices for the tenant.
     */
    public function invoices(Request $request): JsonResponse
    {
        $invoices = $request->user()->tenant->invoices()->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $invoices]);
    }
}
