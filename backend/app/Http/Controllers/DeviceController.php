<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    protected DeviceService $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Display a listing of the devices.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'vehicle_id', 'model_id', 'status', 'is_online']);
        $devices = $this->deviceService->list($filters, $request->get('per_page', 15));

        return response()->json($devices);
    }

    /**
     * Store a newly created device.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_model_id' => 'required|exists:device_models,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'imei' => 'required|string|unique:devices,imei',
            'sim_number' => 'nullable|string|max:20',
            'sim_provider' => 'nullable|string|max:50',
            'installation_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'in:active,inactive,suspended',
            'configuration' => 'nullable|array'
        ]);

        $device = $this->deviceService->create($validated);

        return response()->json([
            'message' => 'Device created successfully',
            'data' => $device
        ], 201);
    }

    /**
     * Display the specified device.
     */
    public function show(Device $device): JsonResponse
    {
        return response()->json($device->load(['vehicle', 'model']));
    }

    /**
     * Update the specified device.
     */
    public function update(Request $request, Device $device): JsonResponse
    {
        $validated = $request->validate([
            'device_model_id' => 'sometimes|exists:device_models,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'imei' => ['required', 'string', Rule::unique('devices')->ignore($device->id)],
            'sim_number' => 'nullable|string|max:20',
            'sim_provider' => 'nullable|string|max:50',
            'installation_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'in:active,inactive,suspended',
            'configuration' => 'nullable|array'
        ]);

        $updatedDevice = $this->deviceService->update($device, $validated);

        return response()->json([
            'message' => 'Device updated successfully',
            'data' => $updatedDevice
        ]);
    }

    /**
     * Remove the specified device.
     */
    public function destroy(Device $device): JsonResponse
    {
        $this->deviceService->delete($device);

        return response()->json([
            'message' => 'Device deleted successfully'
        ]);
    }

    /**
     * Get auxiliary data (Models) for forms.
     */
    public function auxiliary(): JsonResponse
    {
        return response()->json([
            'models' => $this->deviceService->getModels()
        ]);
    }

    /**
     * Send a command to the device.
     */
    public function sendCommand(Request $request, Device $device, \App\Services\CommandService $commandService): JsonResponse
    {
        $validated = $request->validate([
            'command' => 'required|string|in:CUT_ENGINE,RESUME_ENGINE,reboot,position_request',
            'params' => 'nullable|array'
        ]);

        $command = $commandService->send($device, $validated['command'], $validated['params'] ?? []);

        return response()->json([
            'message' => 'Command queued successfully',
            'data' => $command
        ]);
    }
}
