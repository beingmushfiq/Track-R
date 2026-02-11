<?php

namespace App\Events;

use App\Models\GpsData;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $tenantId;
    public int $vehicleId;
    public array $position;

    /**
     * Create a new event instance.
     */
    public function __construct(int $tenantId, int $vehicleId, GpsData $gpsData)
    {
        $this->tenantId = $tenantId;
        $this->vehicleId = $vehicleId;
        $this->position = [
            'latitude' => $gpsData->latitude,
            'longitude' => $gpsData->longitude,
            'altitude' => $gpsData->altitude,
            'speed' => $gpsData->speed,
            'heading' => $gpsData->heading,
            'gps_time' => $gpsData->gps_time->toIso8601String(),
            'ignition' => $gpsData->ignition,
            'odometer' => $gpsData->odometer,
            'fuel_level' => $gpsData->fuel_level,
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
            new PrivateChannel("tenant.{$this->tenantId}.tracking"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'position' => $this->position,
        ];
    }
}
