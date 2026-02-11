<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Get live tracking data for all vehicles in the tenant.
     *
     * @return JsonResponse
     */
    public function live(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $tracking = $this->trackingService->getLiveTracking($tenantId);

        return response()->json([
            'success' => true,
            'data' => $tracking,
            'count' => count($tracking),
        ]);
    }

    /**
     * Get last position for a specific vehicle.
     *
     * @param int $vehicleId
     * @return JsonResponse
     */
    public function lastPosition(Request $request, int $vehicleId): JsonResponse
    {
        $vehicle = Vehicle::where('tenant_id', $request->user()->tenant_id)
            ->with('device')
            ->findOrFail($vehicleId);

        if (!$vehicle->device) {
            return response()->json([
                'success' => false,
                'message' => 'No device assigned to this vehicle',
            ], 404);
        }

        $position = $this->trackingService->getLastPosition($vehicle->device->id);

        if (!$position) {
            return response()->json([
                'success' => false,
                'message' => 'No GPS data available for this vehicle',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle' => [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'registration_number' => $vehicle->registration_number,
                ],
                'position' => $position,
            ],
        ]);
    }

    /**
     * Get historical playback data for a vehicle.
     *
     * @param Request $request
     * @param int $vehicleId
     * @return JsonResponse
     */
    public function playback(Request $request, int $vehicleId): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
        ]);

        $vehicle = Vehicle::where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($vehicleId);

        $playbackData = $this->trackingService->getPlayback(
            $vehicleId,
            $request->input('start'),
            $request->input('end')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle' => [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'registration_number' => $vehicle->registration_number,
                ],
                'route' => $playbackData,
                'points_count' => count($playbackData),
            ],
        ]);
    }

    /**
     * Get route statistics for a vehicle.
     *
     * @param Request $request
     * @param int $vehicleId
     * @return JsonResponse
     */
    public function routeStats(Request $request, int $vehicleId): JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
        ]);

        $vehicle = Vehicle::where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($vehicleId);

        $stats = $this->trackingService->getRouteStatistics(
            $vehicleId,
            $request->input('start'),
            $request->input('end')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle' => [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'registration_number' => $vehicle->registration_number,
                ],
                'statistics' => $stats,
            ],
        ]);
    }
}
