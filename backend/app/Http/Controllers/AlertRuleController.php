<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $rules = AlertRule::latest()->paginate($request->get('per_page', 15));
        return response()->json($rules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|in:overspeed,ignition_on,ignition_off,geofence',
            'conditions' => 'required|array', // Validated deeper based on type?
            'actions' => 'nullable|array',
            'vehicle_ids' => 'nullable|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
            'is_active' => 'boolean'
        ]);

        $rule = AlertRule::create($validated);

        if (!empty($validated['vehicle_ids'])) {
            $rule->vehicles()->sync($validated['vehicle_ids']);
        }

        return response()->json([
            'message' => 'Alert rule created successfully',
            'data' => $rule->load('vehicles')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AlertRule $alertRule): JsonResponse
    {
        return response()->json($alertRule->load('vehicles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AlertRule $alertRule): JsonResponse
    {
        // $this->authorize('update', $alertRule);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'type' => 'sometimes|string|in:overspeed,ignition_on,ignition_off,geofence',
            'conditions' => 'sometimes|array',
            'actions' => 'nullable|array',
            'vehicle_ids' => 'nullable|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
            'is_active' => 'boolean'
        ]);

        $alertRule->update($validated);

        if (isset($validated['vehicle_ids'])) {
            $alertRule->vehicles()->sync($validated['vehicle_ids']);
        }

        return response()->json([
            'message' => 'Alert rule updated successfully',
            'data' => $alertRule->load('vehicles')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlertRule $alertRule): JsonResponse
    {
        $alertRule->vehicles()->detach();
        $alertRule->delete();

        return response()->json(['message' => 'Alert rule deleted successfully']);
    }
}
