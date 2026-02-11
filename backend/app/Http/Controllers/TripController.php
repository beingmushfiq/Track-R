<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TripController extends Controller
{
    /**
     * List trips with filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Trip::where('tenant_id', $request->user()->tenant_id)
            ->with(['vehicle', 'device']);

        // Filter by vehicle
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->input('vehicle_id'));
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('start_time', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('start_time', '<=', $request->input('end_date'));
        }

        $perPage = $request->input('per_page', 15);
        $trips = $query->orderBy('start_time', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $trips,
        ]);
    }

    /**
     * Get trip details with route.
     *
     * @param int $tripId
     * @return JsonResponse
     */
    public function show(Request $request, int $tripId): JsonResponse
    {
        $trip = Trip::where('tenant_id', $request->user()->tenant_id)
            ->with(['vehicle', 'device', 'stops'])
            ->findOrFail($tripId);

        // Get route data (GPS points during trip)
        $route = \App\Models\GpsData::where('device_id', $trip->device_id)
            ->whereBetween('gps_time', [$trip->start_time, $trip->end_time ?? now()])
            ->orderBy('gps_time', 'asc')
            ->get(['latitude', 'longitude', 'speed', 'heading', 'gps_time'])
            ->map(function ($point) {
                return [
                    'lat' => $point->latitude,
                    'lng' => $point->longitude,
                    'speed' => $point->speed,
                    'heading' => $point->heading,
                    'time' => $point->gps_time->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'trip' => $trip,
                'route' => $route,
            ],
        ]);
    }

    /**
     * Get stops for a trip.
     *
     * @param int $tripId
     * @return JsonResponse
     */
    public function stops(Request $request, int $tripId): JsonResponse
    {
        $trip = Trip::where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($tripId);

        $stops = $trip->stops()
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'trip_id' => $trip->id,
                'stops' => $stops,
                'total_stops' => $stops->count(),
                'total_stop_duration' => $stops->sum('duration'),
            ],
        ]);
    }
}
