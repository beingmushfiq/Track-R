<?php

namespace App\Services;

use App\Models\Device;
use App\Models\GpsData;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TrackingService
{
    /**
     * Get last known positions for all vehicles in a tenant.
     */
    public function getLiveTracking(int $tenantId): array
    {
        $vehicles = Vehicle::where('tenant_id', $tenantId)
            ->with(['device', 'type'])
            ->get();

        $result = [];

        foreach ($vehicles as $vehicle) {
            if (!$vehicle->device) {
                continue;
            }

            $lastPosition = $this->getLastPosition($vehicle->device->id);

            if ($lastPosition) {
                $result[] = [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_name' => $vehicle->name,
                    'registration_number' => $vehicle->registration_number,
                    'vehicle_type' => $vehicle->type?->name,
                    'device_id' => $vehicle->device->id,
                    'device_imei' => $vehicle->device->imei,
                    'is_online' => $vehicle->device->is_online,
                    'last_communication' => $vehicle->device->last_communication,
                    'position' => $lastPosition,
                ];
            }
        }

        return $result;
    }

    /**
     * Get last known position for a specific device.
     * Uses Redis cache for performance.
     */
    public function getLastPosition(int $deviceId): ?array
    {
        $cacheKey = "device:{$deviceId}:last_position";

        // Try cache first
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        // Fallback to database
        $gpsData = GpsData::where('device_id', $deviceId)
            ->where('gps_valid', true)
            ->latest('gps_time')
            ->first();

        if (!$gpsData) {
            return null;
        }

        $position = [
            'latitude' => $gpsData->latitude,
            'longitude' => $gpsData->longitude,
            'altitude' => $gpsData->altitude,
            'speed' => $gpsData->speed,
            'heading' => $gpsData->heading,
            'gps_time' => $gpsData->gps_time->toIso8601String(),
            'server_time' => $gpsData->server_time->toIso8601String(),
            'ignition' => $gpsData->ignition,
            'odometer' => $gpsData->odometer,
            'fuel_level' => $gpsData->fuel_level,
        ];

        // Cache for 30 seconds
        Cache::put($cacheKey, $position, 30);

        return $position;
    }

    /**
     * Get historical playback data for a vehicle.
     */
    public function getPlayback(int $vehicleId, string $startTime, string $endTime): array
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $gpsData = GpsData::where('vehicle_id', $vehicleId)
            ->where('gps_valid', true)
            ->whereBetween('gps_time', [$start, $end])
            ->orderBy('gps_time', 'asc')
            ->get(['latitude', 'longitude', 'speed', 'heading', 'gps_time', 'ignition', 'altitude']);

        return $gpsData->map(function ($point) {
            return [
                'lat' => $point->latitude,
                'lng' => $point->longitude,
                'speed' => $point->speed,
                'heading' => $point->heading,
                'time' => $point->gps_time->toIso8601String(),
                'ignition' => $point->ignition,
                'altitude' => $point->altitude,
            ];
        })->toArray();
    }

    /**
     * Get route statistics for a time range.
     */
    public function getRouteStatistics(int $vehicleId, string $startTime, string $endTime): array
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $gpsData = GpsData::where('vehicle_id', $vehicleId)
            ->where('gps_valid', true)
            ->whereBetween('gps_time', [$start, $end])
            ->orderBy('gps_time', 'asc')
            ->get();

        if ($gpsData->isEmpty()) {
            return [
                'total_distance' => 0,
                'total_duration' => 0,
                'max_speed' => 0,
                'avg_speed' => 0,
                'start_time' => null,
                'end_time' => null,
                'start_location' => null,
                'end_location' => null,
            ];
        }

        $totalDistance = 0;
        $maxSpeed = 0;
        $speedSum = 0;
        $count = $gpsData->count();

        // Calculate max speed and speed sum efficiently
        foreach ($gpsData as $point) {
            $maxSpeed = max($maxSpeed, $point->speed);
            $speedSum += $point->speed;
        }

        // Calculate distance using Haversine formula
        for ($i = 1; $i < $count; $i++) {
            $prev = $gpsData[$i - 1];
            $curr = $gpsData[$i];

            $distance = $this->calculateDistance(
                $prev->latitude,
                $prev->longitude,
                $curr->latitude,
                $curr->longitude
            );

            $totalDistance += $distance;
        }

        $first = $gpsData->first();
        $last = $gpsData->last();

        $duration = $first->gps_time->diffInSeconds($last->gps_time);

        return [
            'total_distance' => round($totalDistance / 1000, 2), // Convert to km
            'total_duration' => $duration, // seconds
            'max_speed' => round($maxSpeed, 2),
            'avg_speed' => $count > 0 ? round($speedSum / $count, 2) : 0,
            'start_time' => $first->gps_time->toIso8601String(),
            'end_time' => $last->gps_time->toIso8601String(),
            'start_location' => [
                'lat' => $first->latitude,
                'lng' => $first->longitude,
            ],
            'end_location' => [
                'lat' => $last->latitude,
                'lng' => $last->longitude,
            ],
        ];
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula.
     * Returns distance in meters.
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Update cached last position for a device.
     * Called from ProcessGpsData job.
     */
    public function updateLastPosition(int $deviceId, GpsData $gpsData): void
    {
        $cacheKey = "device:{$deviceId}:last_position";

        $position = [
            'latitude' => $gpsData->latitude,
            'longitude' => $gpsData->longitude,
            'altitude' => $gpsData->altitude,
            'speed' => $gpsData->speed,
            'heading' => $gpsData->heading,
            'gps_time' => $gpsData->gps_time->toIso8601String(),
            'server_time' => $gpsData->server_time->toIso8601String(),
            'ignition' => $gpsData->ignition,
            'odometer' => $gpsData->odometer,
            'fuel_level' => $gpsData->fuel_level,
        ];

        // Cache for 5 minutes (will be refreshed on new GPS data)
        Cache::put($cacheKey, $position, 300);
    }
}
