<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceCommand;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;

class CommandService
{
    /**
     * Send a command to a device.
     *
     * @param Device $device
     * @param string $command (e.g., 'CUT_ENGINE', 'RESUME_ENGINE', 'GET_STATUS')
     * @param array $params
     * @return DeviceCommand
     */
    public function send(Device $device, string $command, array $params = []): DeviceCommand
    {
        // 1. Create Command Record
        $deviceCommand = DeviceCommand::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'user_id' => auth()->id(), // Determine who sent it
            'command' => $command,
            'payload' => $params,
            'status' => 'pending',
        ]);

        // 2. Push to Redis for Device Server to pick up
        // Channel: "device:commands"
        // Payload: { imei, command, id (command_id) }

        $payload = json_encode([
            'command_id' => $deviceCommand->id,
            'imei' => $device->imei,
            'type' => $command,
            'params' => $params,
            'protocol' => $device->model->protocol ?? 'Generic' // Helper for parser
        ]);

        Redis::publish('device:commands', $payload);

        return $deviceCommand;
    }
}
