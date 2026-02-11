<?php

namespace App\Services;

use App\Models\Device;
use App\Models\GpsData;
use App\Models\Trip;
use App\Models\Stop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class TripService
{
    // Minimum speed to consider vehicle as moving (km/h)
    const MIN_MOVING_SPEED = 5;

    // Minimum stop duration to record as a stop (seconds)
    const MIN_STOP_DURATION = 180; // 3 minutes

    /**
     * Process GPS point and detect trip start/end.
     */
    public function processGpsPoint(Device $device, GpsData $gpsData): void
    {
        // Get active trip for this device
        $activeTrip = Trip::where('device_id', $device->id)
            ->whereNull('end_time')
            ->first();

        $isMoving = $gpsData->speed >= self::MIN_MOVING_SPEED;
        $ignitionOn = $gpsData->ignition ?? false;

        if (!$activeTrip && $ignitionOn && $isMoving) {
            // Start new trip
            $this->startTrip($device, $gpsData);
        } elseif ($activeTrip && (!$ignitionOn || !$isMoving)) {
            // Check if we should end the trip
            $this->checkTripEnd($activeTrip, $device, $gpsData);
        } elseif ($activeTrip) {
            // Update trip statistics
            $this->updateTrip($activeTrip, $gpsData);
        }

        // Detect stops during active trip
        if ($activeTrip && !$isMoving) {
            $this->detectStop($activeTrip, $device, $gpsData);
        }
    }

    /**
     * Start a new trip.
     */
    protected function startTrip(Device $device, GpsData $gpsData): Trip
    {
        $trip = Trip::create([
            'tenant_id' => $device->tenant_id,
            'vehicle_id' => $device->vehicle_id,
            'device_id' => $device->id,
            'start_time' => $gpsData->gps_time,
            'start_latitude' => $gpsData->latitude,
            'start_longitude' => $gpsData->longitude,
            'start_odometer' => $gpsData->odometer,
            'start_fuel_level' => $gpsData->fuel_level,
            'distance' => 0,
            'max_speed' => $gpsData->speed,
            'avg_speed' => $gpsData->speed,
        ]);

        Log::info("Trip started for device {$device->imei}", [
            'trip_id' => $trip->id,
            'vehicle_id' => $device->vehicle_id,
        ]);

        return $trip;
    }

    /**
     * Check if trip should end.
     */
    protected function checkTripEnd(Trip $trip, Device $device, GpsData $gpsData): void
    {
        $cacheKey = "trip:{$trip->id}:last_stop_time";
        $lastStopTime = Cache::get($cacheKey);

        if (!$lastStopTime) {
            // First time stopped, cache the time
            Cache::put($cacheKey, $gpsData->gps_time, 600); // 10 minutes
            return;
        }

        $stopDuration = Carbon::parse($lastStopTime)->diffInSeconds($gpsData->gps_time);

        // End trip if stopped for more than 10 minutes with ignition off
        if ($stopDuration > 600 && !$gpsData->ignition) {
            $this->endTrip($trip, $gpsData);
            Cache::forget($cacheKey);
        }
    }

    /**
     * End an active trip.
     */
    protected function endTrip(Trip $trip, GpsData $gpsData): void
    {
        $duration = $trip->start_time->diffInSeconds($gpsData->gps_time);

        $trip->update([
            'end_time' => $gpsData->gps_time,
            'end_latitude' => $gpsData->latitude,
            'end_longitude' => $gpsData->longitude,
            'end_odometer' => $gpsData->odometer,
            'end_fuel_level' => $gpsData->fuel_level,
            'duration' => $duration,
        ]);

        // Calculate final statistics
        $this->calculateTripStatistics($trip);

        Log::info("Trip ended for device {$trip->device->imei}", [
            'trip_id' => $trip->id,
            'distance' => $trip->distance,
            'duration' => $trip->duration,
        ]);
    }

    /**
     * Update trip with new GPS data.
     */
    protected function updateTrip(Trip $trip, GpsData $gpsData): void
    {
        // Get last GPS point for this trip
        $lastGps = GpsData::where('device_id', $trip->device_id)
            ->where('gps_time', '<', $gpsData->gps_time)
            ->where('gps_time', '>=', $trip->start_time)
            ->orderBy('gps_time', 'desc')
            ->first();

        if (!$lastGps) {
            return;
        }

        // Calculate distance increment
        $distanceIncrement = $this->calculateDistance(
            $lastGps->latitude,
            $lastGps->longitude,
            $gpsData->latitude,
            $gpsData->longitude
        );

        $newDistance = $trip->distance + ($distanceIncrement / 1000); // Convert to km
        $maxSpeed = max($trip->max_speed, $gpsData->speed);

        $trip->update([
            'distance' => $newDistance,
            'max_speed' => $maxSpeed,
        ]);
    }

    /**
     * Detect and record stops during trip.
     */
    protected function detectStop(Trip $trip, Device $device, GpsData $gpsData): void
    {
        $cacheKey = "trip:{$trip->id}:stop_start";
        $stopStartTime = Cache::get($cacheKey);

        if (!$stopStartTime) {
            // First time stopped at this location
            Cache::put($cacheKey, [
                'time' => $gpsData->gps_time,
                'lat' => $gpsData->latitude,
                'lng' => $gpsData->longitude,
            ], 600);
            return;
        }

        $stopDuration = Carbon::parse($stopStartTime['time'])->diffInSeconds($gpsData->gps_time);

        // Record stop if duration exceeds minimum
        if ($stopDuration >= self::MIN_STOP_DURATION) {
            // Check if stop already exists
            $existingStop = Stop::where('trip_id', $trip->id)
                ->where('start_time', $stopStartTime['time'])
                ->first();

            if (!$existingStop) {
                Stop::create([
                    'tenant_id' => $trip->tenant_id,
                    'trip_id' => $trip->id,
                    'vehicle_id' => $trip->vehicle_id,
                    'device_id' => $trip->device_id,
                    'start_time' => $stopStartTime['time'],
                    'end_time' => $gpsData->gps_time,
                    'latitude' => $stopStartTime['lat'],
                    'longitude' => $stopStartTime['lng'],
                    'duration' => $stopDuration,
                ]);

                Log::info("Stop detected during trip {$trip->id}", [
                    'duration' => $stopDuration,
                    'location' => [$stopStartTime['lat'], $stopStartTime['lng']],
                ]);
            }

            // Clear cache after recording
            Cache::forget($cacheKey);
        }
    }

    /**
     * Calculate final trip statistics.
     */
    protected function calculateTripStatistics(Trip $trip): void
    {
        // Get all GPS data for this trip
        $gpsData = GpsData::where('device_id', $trip->device_id)
            ->whereBetween('gps_time', [$trip->start_time, $trip->end_time])
            ->orderBy('gps_time', 'asc')
            ->get();

        if ($gpsData->isEmpty()) {
            return;
        }

        $totalDistance = 0;
        $speedSum = 0;
        $count = $gpsData->count();

        // Calculate total distance
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
            $speedSum += $curr->speed;
        }

        $avgSpeed = $count > 0 ? $speedSum / $count : 0;

        // Calculate fuel consumption if data available
        $fuelConsumed = null;
        if ($trip->start_fuel_level && $trip->end_fuel_level) {
            $fuelConsumed = $trip->start_fuel_level - $trip->end_fuel_level;
        }

        $trip->update([
            'distance' => round($totalDistance / 1000, 2), // Convert to km
            'avg_speed' => round($avgSpeed, 2),
            'fuel_consumed' => $fuelConsumed,
        ]);
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula.
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
}
