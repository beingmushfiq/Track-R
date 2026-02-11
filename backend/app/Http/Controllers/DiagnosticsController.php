<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\DiagnosticCode;
use App\Services\DiagnosticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiagnosticsController extends Controller
{
    protected DiagnosticsService $diagnosticsService;

    public function __construct(DiagnosticsService $diagnosticsService)
    {
        $this->diagnosticsService = $diagnosticsService;
    }

    /**
     * Get diagnostic data for a vehicle.
     */
    public function index(Request $request, int $vehicleId): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        
        // Check authorization
        $this->authorize('view', $vehicle);
        
        $days = $request->get('days', 7);
        
        return response()->json([
            'current_metrics' => $this->diagnosticsService->getCurrentMetrics($vehicleId),
            'trends' => $this->diagnosticsService->analyzeTrends($vehicleId, $days),
            'health_score' => $this->diagnosticsService->calculateHealthScore($vehicleId),
        ]);
    }

    /**
     * Get health score for a vehicle.
     */
    public function healthScore(int $vehicleId): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $this->authorize('view', $vehicle);
        
        $healthData = $this->diagnosticsService->calculateHealthScore($vehicleId);
        
        return response()->json($healthData);
    }

    /**
     * Get diagnostic codes for a vehicle.
     */
    public function diagnosticCodes(Request $request, int $vehicleId): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $this->authorize('view', $vehicle);
        
        $status = $request->get('status', 'active'); // active, cleared, all
        
        $query = DiagnosticCode::where('vehicle_id', $vehicleId)
            ->with(['device', 'clearedBy']);
        
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'cleared') {
            $query->whereNotNull('cleared_at');
        }
        
        $codes = $query->orderBy('severity', 'desc')
            ->orderBy('detected_at', 'desc')
            ->get();
        
        return response()->json([
            'data' => $codes,
            'summary' => [
                'total' => $codes->count(),
                'active' => DiagnosticCode::where('vehicle_id', $vehicleId)->active()->count(),
                'critical' => $codes->where('severity', 'critical')->count(),
                'high' => $codes->where('severity', 'high')->count(),
            ],
        ]);
    }

    /**
     * Clear a diagnostic code.
     */
    public function clearCode(Request $request, int $codeId): JsonResponse
    {
        $code = DiagnosticCode::findOrFail($codeId);
        
        // Check if user has permission (implement policy if needed)
        // $this->authorize('clear', $code);
        
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $code->clear(
            $request->user()->id,
            $validated['notes'] ?? null
        );
        
        return response()->json([
            'message' => 'Diagnostic code cleared successfully',
            'code' => $code->fresh(),
        ]);
    }

    /**
     * Get fleet-wide diagnostic summary.
     */
    public function fleetSummary(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        
        $vehicles = Vehicle::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();
        
        $summary = [
            'total_vehicles' => $vehicles->count(),
            'health_scores' => [],
            'active_codes_total' => 0,
            'vehicles_with_issues' => 0,
        ];
        
        foreach ($vehicles as $vehicle) {
            $healthData = $this->diagnosticsService->calculateHealthScore($vehicle->id);
            
            $summary['health_scores'][] = [
                'vehicle_id' => $vehicle->id,
                'vehicle_name' => $vehicle->name,
                'score' => $healthData['score'],
                'status' => $healthData['status'],
            ];
            
            $summary['active_codes_total'] += $healthData['active_codes_count'];
            
            if ($healthData['active_codes_count'] > 0 || $healthData['score'] < 75) {
                $summary['vehicles_with_issues']++;
            }
        }
        
        // Sort by score (worst first)
        usort($summary['health_scores'], function ($a, $b) {
            return $a['score'] <=> $b['score'];
        });
        
        return response()->json($summary);
    }
}
