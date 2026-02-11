<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceCommandController extends Controller
{
    /**
     * Get command history for a device
     */
    public function index(Device $device)
    {
        $this->authorize('view', $device);

        $commands = $device->commands()
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($commands);
    }

    /**
     * Send a command to a device
     */
    public function store(Request $request, Device $device)
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'type' => 'required|in:lock_engine,unlock_engine,locate,reboot',
            'command' => 'nullable|string',
        ]);

        $command = DeviceCommand::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'command' => $validated['command'] ?? $this->generateCommand($validated['type']),
            'status' => 'pending',
        ]);

        // Update device status
        if (in_array($validated['type'], ['lock_engine', 'unlock_engine'])) {
            $device->update([
                'engine_locked' => $validated['type'] === 'lock_engine',
                'last_command_at' => now(),
                'last_command_type' => $validated['type'],
            ]);
        }

        return response()->json([
            'message' => 'Command sent successfully',
            'command' => $command,
        ], 201);
    }

    /**
     * Lock engine
     */
    public function lockEngine(Device $device)
    {
        $this->authorize('update', $device);

        $command = DeviceCommand::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'user_id' => Auth::id(),
            'type' => 'lock_engine',
            'command' => $this->generateCommand('lock_engine'),
            'status' => 'pending',
        ]);

        $device->update([
            'engine_locked' => true,
            'last_command_at' => now(),
            'last_command_type' => 'lock_engine',
        ]);

        return response()->json([
            'message' => 'Engine locked successfully',
            'command' => $command,
        ]);
    }

    /**
     * Unlock engine
     */
    public function unlockEngine(Device $device)
    {
        $this->authorize('update', $device);

        $command = DeviceCommand::create([
            'tenant_id' => $device->tenant_id,
            'device_id' => $device->id,
            'user_id' => Auth::id(),
            'type' => 'unlock_engine',
            'command' => $this->generateCommand('unlock_engine'),
            'status' => 'pending',
        ]);

        $device->update([
            'engine_locked' => false,
            'last_command_at' => now(),
            'last_command_type' => 'unlock_engine',
        ]);

        return response()->json([
            'message' => 'Engine unlocked successfully',
            'command' => $command,
        ]);
    }

    /**
     * Generate command string based on type
     */
    private function generateCommand(string $type): string
    {
        return match($type) {
            'lock_engine' => 'LOCK',
            'unlock_engine' => 'UNLOCK',
            'locate' => 'LOCATE',
            'reboot' => 'REBOOT',
            default => 'UNKNOWN',
        };
    }
}
