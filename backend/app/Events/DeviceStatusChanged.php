<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $tenantId;
    public int $deviceId;
    public int $vehicleId;
    public bool $isOnline;
    public string $lastCommunication;

    /**
     * Create a new event instance.
     */
    public function __construct(Device $device)
    {
        $this->tenantId = $device->tenant_id;
        $this->deviceId = $device->id;
        $this->vehicleId = $device->vehicle_id;
        $this->isOnline = $device->is_online;
        $this->lastCommunication = $device->last_communication?->toIso8601String() ?? '';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.devices"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return $this->isOnline ? 'device.online' : 'device.offline';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'device_id' => $this->deviceId,
            'vehicle_id' => $this->vehicleId,
            'is_online' => $this->isOnline,
            'last_communication' => $this->lastCommunication,
        ];
    }
}
