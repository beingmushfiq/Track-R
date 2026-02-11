<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    /**
     * List all alerts for the tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'type' => 'sometimes|string',
            'is_read' => 'sometimes|boolean',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $tenantId = $request->user()->tenant_id;
        $perPage = $request->input('per_page', 20);

        $query = Alert::where('tenant_id', $tenantId)
            ->with(['vehicle', 'device', 'alertRule']);

        // Apply filters
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('is_read')) {
            $query->where('is_read', $request->input('is_read'));
        }

        if ($request->has('start_date')) {
            $query->where('triggered_at', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('triggered_at', '<=', $request->input('end_date'));
        }

        $alerts = $query->orderBy('triggered_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    /**
     * Get a specific alert.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $alert = Alert::where('tenant_id', $tenantId)
            ->with(['vehicle', 'device', 'alertRule'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $alert,
        ]);
    }

    /**
     * Mark alert as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $alert = Alert::where('tenant_id', $tenantId)->findOrFail($id);
        $alert->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read',
            'data' => $alert,
        ]);
    }

    /**
     * Mark multiple alerts as read.
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'integer|exists:alerts,id',
        ]);

        $tenantId = $request->user()->tenant_id;

        $count = Alert::where('tenant_id', $tenantId)
            ->whereIn('id', $validated['alert_ids'])
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => "{$count} alerts marked as read",
        ]);
    }

    /**
     * Delete an alert.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $alert = Alert::where('tenant_id', $tenantId)->findOrFail($id);
        $alert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alert deleted successfully',
        ]);
    }

    // ========== Alert Rules Management ==========

    /**
     * List all alert rules for the tenant.
     */
    public function listRules(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $rules = AlertRule::where('tenant_id', $tenantId)
            ->with('vehicles')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    /**
     * Get a specific alert rule.
     */
    public function showRule(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $rule = AlertRule::where('tenant_id', $tenantId)
            ->with('vehicles')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $rule,
        ]);
    }

    /**
     * Create a new alert rule.
     */
    public function storeRule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:overspeed,idle,offline,geofence_enter,geofence_exit,ignition_on,ignition_off',
            'conditions' => 'required|array',
            'is_active' => 'boolean',
            'vehicle_ids' => 'array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $tenantId = $request->user()->tenant_id;

        $rule = AlertRule::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'conditions' => $validated['conditions'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach vehicles if provided
        if (isset($validated['vehicle_ids'])) {
            $rule->vehicles()->attach($validated['vehicle_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alert rule created successfully',
            'data' => $rule->load('vehicles'),
        ], 201);
    }

    /**
     * Update an existing alert rule.
     */
    public function updateRule(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:overspeed,idle,offline,geofence_enter,geofence_exit,ignition_on,ignition_off',
            'conditions' => 'sometimes|array',
            'is_active' => 'boolean',
            'vehicle_ids' => 'array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $tenantId = $request->user()->tenant_id;

        $rule = AlertRule::where('tenant_id', $tenantId)->findOrFail($id);
        $rule->update($validated);

        // Sync vehicles if provided
        if (isset($validated['vehicle_ids'])) {
            $rule->vehicles()->sync($validated['vehicle_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alert rule updated successfully',
            'data' => $rule->load('vehicles'),
        ]);
    }

    /**
     * Delete an alert rule.
     */
    public function destroyRule(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $rule = AlertRule::where('tenant_id', $tenantId)->findOrFail($id);
        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alert rule deleted successfully',
        ]);
    }
}
