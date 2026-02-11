<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Alert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailySummaryService
{
    public function generateSummary(int $tenantId, string $date = null): array
    {
        $date = $date ? Carbon::parse($date) : Carbon::yesterday();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // 1. Active Vehicles (vehicles with trips or GPS data today)
        $activeVehiclesCount = Trip::where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$startOfDay, $endOfDay])
            ->distinct('vehicle_id')
            ->count('vehicle_id');

        $totalVehicles = Vehicle::where('tenant_id', $tenantId)->count();

        // 2. Total Distance & Fuel (aggregated from trips)
        $tripStats = Trip::where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$startOfDay, $endOfDay])
            ->select(
                DB::raw('SUM(distance) as total_distance'),
                DB::raw('SUM(fuel_consumed) as total_fuel'),
                DB::raw('SUM(duration) as total_duration')
            )
            ->first();

        // 3. Safety Events (Alerts)
        $safetyEvents = Alert::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();
            
        // 4. Critical Alerts Breakdown
        $criticalAlerts = Alert::where('tenant_id', $tenantId)
            ->where('severity', 'critical')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->with('vehicle:id,name')
            ->get()
            ->groupBy('vehicle.name')
            ->map(fn($group) => $group->count());

        return [
            'date' => $date->toDateString(),
            'tenant_id' => $tenantId,
            'summary' => [
                'total_vehicles' => $totalVehicles,
                'active_vehicles' => $activeVehiclesCount,
                'utilization_rate' => $totalVehicles > 0 ? round(($activeVehiclesCount / $totalVehicles) * 100, 1) : 0,
                'total_distance_km' => round($tripStats->total_distance ?? 0, 2),
                'total_fuel_liters' => round($tripStats->total_fuel ?? 0, 2),
                'total_duration_hours' => round(($tripStats->total_duration ?? 0) / 60, 1), // Assuming duration is minutes
                'safety_events' => $safetyEvents,
                'critical_alerts_breakdown' => $criticalAlerts,
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
