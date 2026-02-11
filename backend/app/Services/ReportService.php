<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\GpsData;
use App\Models\Alert;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generate trip summary report.
     */
    public function generateTripReport(int $tenantId, array $filters): array
    {
        $query = Trip::where('tenant_id', $tenantId)
            ->with(['vehicle', 'device']);

        // Apply filters
        if (isset($filters['vehicle_id'])) {
            $query->where('vehicle_id', $filters['vehicle_id']);
        }

        if (isset($filters['start_date'])) {
            $query->where('start_time', '>=', Carbon::parse($filters['start_date']));
        }

        if (isset($filters['end_date'])) {
            $query->where('start_time', '<=', Carbon::parse($filters['end_date']));
        }

        $trips = $query->whereNotNull('end_time')
            ->orderBy('start_time', 'desc')
            ->get();

        return [
            'summary' => [
                'total_trips' => $trips->count(),
                'total_distance' => round($trips->sum('distance'), 2),
                'total_duration' => $trips->sum('duration'),
                'avg_distance_per_trip' => $trips->count() > 0 ? round($trips->avg('distance'), 2) : 0,
                'avg_duration_per_trip' => $trips->count() > 0 ? round($trips->avg('duration'), 2) : 0,
                'max_speed_recorded' => round($trips->max('max_speed'), 2),
            ],
            'trips' => $trips->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'vehicle' => $trip->vehicle->name,
                    'registration' => $trip->vehicle->registration_number,
                    'start_time' => $trip->start_time->toIso8601String(),
                    'end_time' => $trip->end_time?->toIso8601String(),
                    'distance' => $trip->distance,
                    'duration' => $trip->duration,
                    'max_speed' => $trip->max_speed,
                    'avg_speed' => $trip->avg_speed,
                    'fuel_consumed' => $trip->fuel_consumed,
                ];
            })->toArray(),
        ];
    }

    /**
     * Generate distance report by vehicle.
     */
    public function generateDistanceReport(int $tenantId, array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? now()->subMonth());
        $endDate = Carbon::parse($filters['end_date'] ?? now());

        $vehicleStats = Vehicle::where('tenant_id', $tenantId)
            ->with('trips')
            ->get()
            ->map(function ($vehicle) use ($startDate, $endDate) {
                $trips = $vehicle->trips()
                    ->whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('end_time')
                    ->get();

                return [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_name' => $vehicle->name,
                    'registration' => $vehicle->registration_number,
                    'total_distance' => round($trips->sum('distance'), 2),
                    'trip_count' => $trips->count(),
                    'avg_distance_per_trip' => $trips->count() > 0 ? round($trips->avg('distance'), 2) : 0,
                    'max_distance_trip' => round($trips->max('distance'), 2),
                ];
            })
            ->sortByDesc('total_distance')
            ->values()
            ->toArray();

        return [
            'period' => [
                'start' => $startDate->toIso8601String(),
                'end' => $endDate->toIso8601String(),
            ],
            'summary' => [
                'total_distance' => round(collect($vehicleStats)->sum('total_distance'), 2),
                'total_trips' => collect($vehicleStats)->sum('trip_count'),
                'vehicles_tracked' => count($vehicleStats),
            ],
            'vehicles' => $vehicleStats,
        ];
    }

    /**
     * Generate fuel consumption report.
     */
    public function generateFuelReport(int $tenantId, array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? now()->subMonth());
        $endDate = Carbon::parse($filters['end_date'] ?? now());

        $vehicleStats = Vehicle::where('tenant_id', $tenantId)
            ->get()
            ->map(function ($vehicle) use ($startDate, $endDate) {
                $trips = Trip::where('vehicle_id', $vehicle->id)
                    ->whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('end_time')
                    ->whereNotNull('fuel_consumed')
                    ->get();

                $totalFuel = $trips->sum('fuel_consumed');
                $totalDistance = $trips->sum('distance');

                return [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_name' => $vehicle->name,
                    'registration' => $vehicle->registration_number,
                    'fuel_consumed' => round($totalFuel, 2),
                    'distance_traveled' => round($totalDistance, 2),
                    'fuel_efficiency' => $totalFuel > 0 ? round($totalDistance / $totalFuel, 2) : 0, // km/L
                    'trip_count' => $trips->count(),
                ];
            })
            ->filter(fn($stat) => $stat['fuel_consumed'] > 0)
            ->sortByDesc('fuel_consumed')
            ->values()
            ->toArray();

        return [
            'period' => [
                'start' => $startDate->toIso8601String(),
                'end' => $endDate->toIso8601String(),
            ],
            'summary' => [
                'total_fuel_consumed' => round(collect($vehicleStats)->sum('fuel_consumed'), 2),
                'total_distance' => round(collect($vehicleStats)->sum('distance_traveled'), 2),
                'avg_fuel_efficiency' => collect($vehicleStats)->avg('fuel_efficiency') 
                    ? round(collect($vehicleStats)->avg('fuel_efficiency'), 2) 
                    : 0,
            ],
            'vehicles' => $vehicleStats,
        ];
    }

    /**
     * Generate vehicle utilization report.
     */
    public function generateUtilizationReport(int $tenantId, array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? now()->subMonth());
        $endDate = Carbon::parse($filters['end_date'] ?? now());
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $vehicleStats = Vehicle::where('tenant_id', $tenantId)
            ->get()
            ->map(function ($vehicle) use ($startDate, $endDate, $totalDays) {
                $trips = Trip::where('vehicle_id', $vehicle->id)
                    ->whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('end_time')
                    ->get();

                $totalDuration = $trips->sum('duration'); // seconds
                $daysUsed = $trips->pluck('start_time')
                    ->map(fn($time) => $time->format('Y-m-d'))
                    ->unique()
                    ->count();

                return [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_name' => $vehicle->name,
                    'registration' => $vehicle->registration_number,
                    'total_trips' => $trips->count(),
                    'total_distance' => round($trips->sum('distance'), 2),
                    'total_duration_hours' => round($totalDuration / 3600, 2),
                    'days_used' => $daysUsed,
                    'utilization_rate' => round(($daysUsed / $totalDays) * 100, 2), // %
                    'avg_trips_per_day' => $daysUsed > 0 ? round($trips->count() / $daysUsed, 2) : 0,
                ];
            })
            ->sortByDesc('utilization_rate')
            ->values()
            ->toArray();

        return [
            'period' => [
                'start' => $startDate->toIso8601String(),
                'end' => $endDate->toIso8601String(),
                'total_days' => $totalDays,
            ],
            'summary' => [
                'avg_utilization_rate' => round(collect($vehicleStats)->avg('utilization_rate'), 2),
                'total_trips' => collect($vehicleStats)->sum('total_trips'),
                'total_distance' => round(collect($vehicleStats)->sum('total_distance'), 2),
            ],
            'vehicles' => $vehicleStats,
        ];
    }

    /**
     * Generate alert summary report.
     */
    public function generateAlertReport(int $tenantId, array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? now()->subMonth());
        $endDate = Carbon::parse($filters['end_date'] ?? now());

        $alerts = Alert::where('tenant_id', $tenantId)
            ->whereBetween('triggered_at', [$startDate, $endDate])
            ->with(['vehicle', 'alertRule'])
            ->get();

        $byType = $alerts->groupBy('type')->map(function ($group, $type) {
            return [
                'type' => $type,
                'count' => $group->count(),
                'vehicles_affected' => $group->pluck('vehicle_id')->unique()->count(),
            ];
        })->values();

        $byVehicle = $alerts->groupBy('vehicle_id')->map(function ($group) {
            $vehicle = $group->first()->vehicle;
            return [
                'vehicle_id' => $vehicle->id,
                'vehicle_name' => $vehicle->name,
                'registration' => $vehicle->registration_number,
                'alert_count' => $group->count(),
                'alert_types' => $group->groupBy('type')->map->count()->toArray(),
            ];
        })->sortByDesc('alert_count')->values();

        return [
            'period' => [
                'start' => $startDate->toIso8601String(),
                'end' => $endDate->toIso8601String(),
            ],
            'summary' => [
                'total_alerts' => $alerts->count(),
                'vehicles_with_alerts' => $alerts->pluck('vehicle_id')->unique()->count(),
                'most_common_type' => $byType->sortByDesc('count')->first()['type'] ?? 'N/A',
            ],
            'by_type' => $byType->toArray(),
            'by_vehicle' => $byVehicle->toArray(),
        ];
    }

    /**
     * Generate daily activity report.
     */
    public function generateDailyActivityReport(int $tenantId, string $date): array
    {
        $targetDate = Carbon::parse($date);
        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        $trips = Trip::where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$startOfDay, $endOfDay])
            ->with('vehicle')
            ->get();

        $alerts = Alert::where('tenant_id', $tenantId)
            ->whereBetween('triggered_at', [$startOfDay, $endOfDay])
            ->count();

        $activeVehicles = $trips->pluck('vehicle_id')->unique()->count();

        return [
            'date' => $targetDate->format('Y-m-d'),
            'summary' => [
                'total_trips' => $trips->count(),
                'active_vehicles' => $activeVehicles,
                'total_distance' => round($trips->sum('distance'), 2),
                'total_duration_hours' => round($trips->sum('duration') / 3600, 2),
                'alerts_triggered' => $alerts,
            ],
            'trips' => $trips->map(function ($trip) {
                return [
                    'vehicle' => $trip->vehicle->name,
                    'start_time' => $trip->start_time->format('H:i'),
                    'end_time' => $trip->end_time?->format('H:i'),
                    'distance' => $trip->distance,
                    'duration_minutes' => round($trip->duration / 60, 0),
                ];
            })->toArray(),
        ];
    }
}
