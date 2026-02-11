<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Geofence;
use App\Models\GeofenceEvent;
use App\Models\GpsData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeofenceService
{
    /**
     * Evaluate geofences for a device based on incoming GPS data.
     */
    public function evaluate(Device $device, GpsData $gpsData): void
    {
        $geofences = Geofence::where('tenant_id', $device->tenant_id)
            ->where('is_active', true)
            ->with('vehicles')
            ->get();

        foreach ($geofences as $geofence) {
            // Scope check
            if ($geofence->vehicles->isNotEmpty() && !$geofence->vehicles->contains('id', $device->vehicle_id)) {
                continue;
            }

            $isInside = $this->isInside($geofence, $gpsData->latitude, $gpsData->longitude);
            $this->handleStateChange($geofence, $device, $isInside, $gpsData);
        }
    }

    /**
     * Check if point is inside geofence.
     */
    public function isInside(Geofence $geofence, float $lat, float $lng): bool
    {
        if ($geofence->type === 'circle') {
            return $this->checkCircle($geofence->center_lat, $geofence->center_lng, $geofence->radius, $lat, $lng);
        } elseif ($geofence->type === 'polygon') {
            return $this->checkPolygon($geofence->coordinates, $lat, $lng);
        }
        return false;
    }

    /**
     * Handle state transition (Enter/Exit).
     */
    protected function handleStateChange(Geofence $geofence, Device $device, bool $isInsideNow, GpsData $data): void
    {
        $cacheKey = "geofence_state:{$device->id}:{$geofence->id}";
        $previousState = Cache::get($cacheKey);

        // If cache miss, try to fetch last event from DB
        if ($previousState === null) {
            $lastEvent = GeofenceEvent::where('geofence_id', $geofence->id)
                ->where('device_id', $device->id)
                ->latest('event_time')
                ->first();

            $previousState = $lastEvent ? ($lastEvent->event_type === 'enter') : false;
        }

        // Logic table:
        // Prev: False, Now: True -> ENTER
        // Prev: True, Now: False -> EXIT
        // Prev: True, Now: True -> No Change
        // Prev: False, Now: False -> No Change

        if (!$previousState && $isInsideNow) {
            $this->recordEvent($geofence, $device, 'enter', $data);
            Cache::put($cacheKey, true, now()->addDays(7));
        } elseif ($previousState && !$isInsideNow) {
            $this->recordEvent($geofence, $device, 'exit', $data);
            Cache::put($cacheKey, false, now()->addDays(7));
        }
    }

    protected function recordEvent(Geofence $geofence, Device $device, string $type, GpsData $data): void
    {
        $geofenceEvent = GeofenceEvent::create([
            'geofence_id' => $geofence->id,
            'vehicle_id' => $device->vehicle_id,
            'device_id' => $device->id,
            'event_type' => $type,
            'event_time' => $data->gps_time,
            'latitude' => $data->latitude,
            'longitude' => $data->longitude,
        ]);

        Log::info("Geofence {$type}: {$geofence->name} for Device: {$device->imei}");

        // Broadcast geofence event
        event(new \App\Events\GeofenceEventOccurred($geofenceEvent));

        // TODO: Trigger Notification
    }

    /**
     * Haversine formula for circle check.
     */
    protected function checkCircle($centerLat, $centerLng, $radiusMeters, $lat, $lng): bool
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat - $centerLat);
        $dLng = deg2rad($lng - $centerLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($centerLat)) * cos(deg2rad($lat)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance <= $radiusMeters;
    }

    /**
     * Ray-casting algorithm for polygon check.
     */
    protected function checkPolygon(?array $coordinates, $lat, $lng): bool
    {
        if (empty($coordinates) || count($coordinates) < 3) {
            return false;
        }

        // Standardize coordinates if they depend on structure (e.g. ['lat'=>..., 'lng'=>...] vs [lat, lng])
        // Assuming array of ['lat' => x, 'lng' => y]

        $inside = false;
        $count = count($coordinates);
        $j = $count - 1;

        for ($i = 0; $i < $count; $i++) {
            $xi = $coordinates[$i]['lat'];
            $yi = $coordinates[$i]['lng'];
            $xj = $coordinates[$j]['lat'];
            $yj = $coordinates[$j]['lng'];

            $intersect = (($yi > $lng) != ($yj > $lng)) &&
                ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }
}
