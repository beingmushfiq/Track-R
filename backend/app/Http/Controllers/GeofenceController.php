<?php

namespace App\Http\Controllers;

use App\Models\Geofence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeofenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $geofences = Geofence::latest()->paginate($request->get('per_page', 15));
        return response()->json($geofences);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:circle,polygon',
            'center_lat' => 'required_if:type,circle|nullable|numeric|between:-90,90',
            'center_lng' => 'required_if:type,circle|nullable|numeric|between:-180,180',
            'radius' => 'required_if:type,circle|nullable|integer|min:10',
            'coordinates' => 'required_if:type,polygon|nullable|array|min:3',
            'coordinates.*.lat' => 'required_with:coordinates|numeric|between:-90,90',
            'coordinates.*.lng' => 'required_with:coordinates|numeric|between:-180,180',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'vehicle_ids' => 'nullable|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $geofence = Geofence::create($validated);

        if (!empty($validated['vehicle_ids'])) {
            $geofence->vehicles()->sync($validated['vehicle_ids']);
        }

        return response()->json([
            'message' => 'Geofence created successfully',
            'data' => $geofence->load('vehicles')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Geofence $geofence): JsonResponse
    {
        return response()->json($geofence->load('vehicles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Geofence $geofence): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'type' => 'sometimes|in:circle,polygon',
            'center_lat' => 'nullable|numeric',
            'center_lng' => 'nullable|numeric',
            'radius' => 'nullable|integer',
            'coordinates' => 'nullable|array',
            'color' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'vehicle_ids' => 'nullable|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $geofence->update($validated);

        if (isset($validated['vehicle_ids'])) {
            $geofence->vehicles()->sync($validated['vehicle_ids']);
        }

        return response()->json([
            'message' => 'Geofence updated successfully',
            'data' => $geofence->load('vehicles')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Geofence $geofence): JsonResponse
    {
        $geofence->vehicles()->detach();
        $geofence->delete();

        return response()->json(['message' => 'Geofence deleted successfully']);
    }
}
