<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\GpsData;
use App\Models\DiagnosticCode;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiagnosticsService
{
    /**
     * Calculate vehicle health score (0-100).
     * Based on active DTCs, recent diagnostics data, and vehicle age.
     */
    public function calculateHealthScore(int $vehicleId): array
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        
        // Get active diagnostic codes
        $activeCodes = DiagnosticCode::where('vehicle_id', $vehicleId)
            ->active()
            ->get();
        
        // Get recent diagnostic data (last 24 hours)
        $recentData = GpsData::where('vehicle_id', $vehicleId)
            ->where('gps_time', '>=', Carbon::now()->subHours(24))
            ->whereNotNull('rpm')
            ->latest('gps_time')
            ->limit(100)
            ->get();
        
        $score = 100; // Start with perfect score
        $issues = [];
        
        // Deduct points for active DTCs
        foreach ($activeCodes as $code) {
            switch ($code->severity) {
                case 'critical':
                    $score -= 25;
                    $issues[] = "Critical code: {$code->code}";
                    break;
                case 'high':
                    $score -= 15;
                    $issues[] = "High severity code: {$code->code}";
                    break;
                case 'medium':
                    $score -= 8;
                    $issues[] = "Medium severity code: {$code->code}";
                    break;
                case 'low':
                    $score -= 3;
                    $issues[] = "Low severity code: {$code->code}";
                    break;
            }
        }
        
        // Analyze recent diagnostic data
        if ($recentData->isNotEmpty()) {
            // Check coolant temperature (normal range: 85-105°C)
            $avgCoolantTemp = $recentData->whereNotNull('coolant_temp')->avg('coolant_temp');
            if ($avgCoolantTemp > 110) {
                $score -= 10;
                $issues[] = 'High coolant temperature';
            } elseif ($avgCoolantTemp > 105) {
                $score -= 5;
                $issues[] = 'Elevated coolant temperature';
            }
            
            // Check battery voltage (normal range: 12.4-14.7V)
            $avgBatteryVoltage = $recentData->whereNotNull('battery_voltage')->avg('battery_voltage');
            if ($avgBatteryVoltage < 12.0) {
                $score -= 8;
                $issues[] = 'Low battery voltage';
            } elseif ($avgBatteryVoltage > 15.0) {
                $score -= 8;
                $issues[] = 'High battery voltage (charging system issue)';
            }
            
            // Check engine load (consistently high load > 80% is concerning)
            $highLoadCount = $recentData->where('engine_load', '>', 80)->count();
            if ($highLoadCount > $recentData->count() * 0.5) {
                $score -= 5;
                $issues[] = 'Consistently high engine load';
            }
        }
        
        // Ensure score doesn't go below 0
        $score = max(0, $score);
        
        // Determine health status
        $status = $this->getHealthStatus($score);
        
        return [
            'score' => round($score, 1),
            'status' => $status,
            'active_codes_count' => $activeCodes->count(),
            'issues' => $issues,
            'last_updated' => now()->toIso8601String(),
        ];
    }
    
    /**
     * Get health status based on score.
     */
    private function getHealthStatus(float $score): string
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'fair';
        if ($score >= 40) return 'poor';
        return 'critical';
    }
    
    /**
     * Get active diagnostic codes for a vehicle.
     */
    public function getActiveCodes(int $vehicleId): array
    {
        $codes = DiagnosticCode::where('vehicle_id', $vehicleId)
            ->active()
            ->with(['device', 'vehicle'])
            ->orderBy('severity', 'desc')
            ->orderBy('detected_at', 'desc')
            ->get();
        
        return $codes->map(function ($code) {
            return [
                'id' => $code->id,
                'code' => $code->code,
                'description' => $code->description,
                'severity' => $code->severity,
                'detected_at' => $code->detected_at->toIso8601String(),
                'days_active' => $code->detected_at->diffInDays(now()),
            ];
        })->toArray();
    }
    
    /**
     * Analyze diagnostic trends over time.
     */
    public function analyzeTrends(int $vehicleId, int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $data = GpsData::where('vehicle_id', $vehicleId)
            ->where('gps_time', '>=', $startDate)
            ->whereNotNull('rpm')
            ->select([
                DB::raw('DATE(gps_time) as date'),
                DB::raw('AVG(rpm) as avg_rpm'),
                DB::raw('AVG(coolant_temp) as avg_coolant_temp'),
                DB::raw('AVG(battery_voltage) as avg_battery_voltage'),
                DB::raw('AVG(engine_load) as avg_engine_load'),
                DB::raw('AVG(fuel_level) as avg_fuel_level'),
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return $data->map(function ($item) {
            return [
                'date' => $item->date,
                'avg_rpm' => round($item->avg_rpm ?? 0, 0),
                'avg_coolant_temp' => round($item->avg_coolant_temp ?? 0, 1),
                'avg_battery_voltage' => round($item->avg_battery_voltage ?? 0, 2),
                'avg_engine_load' => round($item->avg_engine_load ?? 0, 1),
                'avg_fuel_level' => round($item->avg_fuel_level ?? 0, 1),
            ];
        })->toArray();
    }
    
    /**
     * Get current diagnostic metrics for a vehicle.
     */
    public function getCurrentMetrics(int $vehicleId): array
    {
        $latest = GpsData::where('vehicle_id', $vehicleId)
            ->whereNotNull('rpm')
            ->latest('gps_time')
            ->first();
        
        if (!$latest) {
            return [
                'available' => false,
                'message' => 'No diagnostic data available',
            ];
        }
        
        return [
            'available' => true,
            'rpm' => $latest->rpm,
            'coolant_temp' => $latest->coolant_temp,
            'battery_voltage' => $latest->battery_voltage,
            'engine_load' => $latest->engine_load,
            'fuel_level' => $latest->fuel_level,
            'ignition' => $latest->ignition,
            'timestamp' => $latest->gps_time->toIso8601String(),
        ];
    }
}
