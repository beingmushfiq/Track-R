<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Display a listing of the vehicles.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'group_id', 'type_id', 'status']);
        $vehicles = $this->vehicleService->list($filters, $request->get('per_page', 15));

        return response()->json($vehicles);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_group_id' => 'nullable|exists:vehicle_groups,id',
            'name' => 'required|string|max:100',
            'registration_number' => [
                'nullable',
                'string',
                'max:50',
                // Unique per tenant rule handled manually or via advanced Rule::unique
                Rule::unique('vehicles')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id)
                        ->whereNull('deleted_at');
                })
            ],
            'vin' => 'nullable|string|max:50',
            'make' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'fuel_capacity' => 'nullable|numeric|min:0',
            'fuel_consumption' => 'nullable|numeric|min:0',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:50',
            'status' => 'in:active,inactive,maintenance',
            'custom_fields' => 'nullable|array'
        ]);

        $vehicle = $this->vehicleService->create($validated);

        return response()->json([
            'message' => 'Vehicle created successfully',
            'data' => $vehicle
        ], 201);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        // Policy authorization can be added here: $this->authorize('view', $vehicle);
        return response()->json($vehicle->load(['type', 'group', 'device']));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        // $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'vehicle_type_id' => 'sometimes|exists:vehicle_types,id',
            'vehicle_group_id' => 'nullable|exists:vehicle_groups,id',
            'name' => 'sometimes|string|max:100',
            'registration_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('vehicles')->where(function ($query) use ($vehicle) {
                    return $query->where('tenant_id', $vehicle->tenant_id)
                        ->whereNull('deleted_at');
                })->ignore($vehicle->id)
            ],
            'vin' => 'nullable|string|max:50',
            'make' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'year' => 'nullable|integer',
            'color' => 'nullable|string|max:50',
            'fuel_capacity' => 'nullable|numeric',
            'fuel_consumption' => 'nullable|numeric',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:50',
            'status' => 'in:active,inactive,maintenance',
            'custom_fields' => 'nullable|array'
        ]);

        $updatedVehicle = $this->vehicleService->update($vehicle, $validated);

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'data' => $updatedVehicle
        ]);
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        // $this->authorize('delete', $vehicle);

        $this->vehicleService->delete($vehicle);

        return response()->json([
            'message' => 'Vehicle deleted successfully'
        ]);
    }

    /**
     * Get auxiliary data (Types, Groups) for forms.
     */
    public function auxiliary(): JsonResponse
    {
        return response()->json([
            'types' => $this->vehicleService->getTypes(),
            'groups' => $this->vehicleService->getGroups()
        ]);
    }
}
