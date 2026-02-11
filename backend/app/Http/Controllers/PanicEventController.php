<?php

namespace App\Http\Controllers;

use App\Models\PanicEvent;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanicEventController extends Controller
{
    /**
     * Get all panic events
     */
    public function index(Request $request)
    {
        $query = PanicEvent::with(['vehicle', 'device'])
            ->latest('triggered_at');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNull('resolved_at');
            } elseif ($request->status === 'resolved') {
                $query->whereNotNull('resolved_at');
            }
        }

        // Filter by vehicle
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('triggered_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('triggered_at', '<=', $request->to);
        }

        $events = $query->paginate(20);

        return response()->json($events);
    }

    /**
     * Get a specific panic event
     */
    public function show(PanicEvent $panicEvent)
    {
        $this->authorize('view', $panicEvent);

        return response()->json($panicEvent->load(['vehicle', 'device']));
    }

    /**
     * Resolve a panic event
     */
    public function resolve(Request $request, PanicEvent $panicEvent)
    {
        $this->authorize('update', $panicEvent);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $panicEvent->resolve(
            Auth::user()->name,
            $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Panic event resolved successfully',
            'event' => $panicEvent->fresh(),
        ]);
    }

    /**
     * Get panic event statistics
     */
    public function statistics(Request $request)
    {
        $from = $request->input('from', now()->subDays(30));
        $to = $request->input('to', now());

        $stats = [
            'total' => PanicEvent::whereBetween('triggered_at', [$from, $to])->count(),
            'active' => PanicEvent::whereNull('resolved_at')->count(),
            'resolved' => PanicEvent::whereNotNull('resolved_at')
                ->whereBetween('triggered_at', [$from, $to])
                ->count(),
            'avg_resolution_time' => PanicEvent::whereNotNull('resolved_at')
                ->whereBetween('triggered_at', [$from, $to])
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, triggered_at, resolved_at)) as avg_time')
                ->value('avg_time'),
            'by_vehicle' => PanicEvent::with('vehicle:id,name')
                ->whereBetween('triggered_at', [$from, $to])
                ->get()
                ->groupBy('vehicle_id')
                ->map(fn($events) => [
                    'vehicle' => $events->first()->vehicle,
                    'count' => $events->count(),
                ])
                ->values(),
        ];

        return response()->json($stats);
    }
}
