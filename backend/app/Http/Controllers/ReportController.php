<?php

namespace App\Http\Controllers;

use App\Services\DailySummaryService;
use App\Services\ReportSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $dailySummaryService;
    protected $subscriptionService;

    public function __construct(
        DailySummaryService $dailySummaryService,
        ReportSubscriptionService $subscriptionService
    ) {
        $this->dailySummaryService = $dailySummaryService;
        $this->subscriptionService = $subscriptionService;
    }

    public function dailySummary(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $date = $request->input('date'); // Optional date

        $summary = $this->dailySummaryService->generateSummary($tenantId, $date);

        return response()->json($summary);
    }

    public function index(Request $request): JsonResponse
    {
        $subscriptions = $this->subscriptionService->getUserSubscriptions($request->user());
        return response()->json($subscriptions);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'report_type' => 'required|in:daily,weekly,monthly',
            'delivery_method' => 'required|in:email,sms',
            'delivery_time' => 'required|date_format:H:i',
        ]);

        $subscription = $this->subscriptionService->subscribe($request->user(), $request->all());

        return response()->json($subscription, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->subscriptionService->unsubscribe($request->user(), $id);
        return response()->json(['message' => 'Subscription deleted']);
    }

    public function export(Request $request): JsonResponse
    {
        // Placeholder for PDF export
        return response()->json(['message' => 'PDF export not implemented yet'], 501);
    }
}
