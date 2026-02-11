<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertTriggered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $tenantId;
    public array $alert;

    /**
     * Create a new event instance.
     */
    public function __construct(Alert $alert)
    {
        $this->tenantId = $alert->tenant_id;
        $this->alert = [
            'id' => $alert->id,
            'vehicle_id' => $alert->vehicle_id,
            'device_id' => $alert->device_id,
            'alert_rule_id' => $alert->alert_rule_id,
            'type' => $alert->alertRule->type ?? 'unknown',
            'severity' => $alert->severity,
            'message' => $alert->message,
            'value' => $alert->value,
            'latitude' => $alert->latitude,
            'longitude' => $alert->longitude,
            'triggered_at' => $alert->triggered_at->toIso8601String(),
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
            new PrivateChannel("tenant.{$this->tenantId}.alerts"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'alert.triggered';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'alert' => $this->alert,
        ];
    }
}
