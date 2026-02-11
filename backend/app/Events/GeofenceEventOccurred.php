<?php

namespace App\Events;

use App\Models\GeofenceEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GeofenceEventOccurred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $tenantId;
    public array $event;

    /**
     * Create a new event instance.
     */
    public function __construct(GeofenceEvent $geofenceEvent)
    {
        $this->tenantId = $geofenceEvent->geofence->tenant_id;
        $this->event = [
            'id' => $geofenceEvent->id,
            'geofence_id' => $geofenceEvent->geofence_id,
            'geofence_name' => $geofenceEvent->geofence->name,
            'vehicle_id' => $geofenceEvent->vehicle_id,
            'device_id' => $geofenceEvent->device_id,
            'event_type' => $geofenceEvent->event_type, // 'enter' or 'exit'
            'event_time' => $geofenceEvent->event_time->toIso8601String(),
            'latitude' => $geofenceEvent->latitude,
            'longitude' => $geofenceEvent->longitude,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.geofences"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'geofence.' . $this->event['event_type'];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event' => $this->event,
        ];
    }
}
