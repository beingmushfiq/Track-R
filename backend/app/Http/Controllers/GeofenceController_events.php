    }

    /**
     * Get geofence events history.
     */
    public function events(Request $request, Geofence $geofence): JsonResponse
    {
        $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = $request->input('per_page', 20);

        $query = $geofence->events()->with(['vehicle', 'device']);

        if ($request->has('start_date')) {
            $query->where('event_time', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('event_time', '<=', $request->input('end_date'));
        }

        $events = $query->orderBy('event_time', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }
}
