<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\GpsData;
use App\Models\DiagnosticCode;
use Carbon\Carbon;

class DiagnosticDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicle = Vehicle::first();
        
        if (!$vehicle) {
            $this->command->error('No vehicles found. Please run DemoDataSeeder first.');
            return;
        }

        $this->command->info("Seeding diagnostic data for vehicle: {$vehicle->name} (ID: {$vehicle->id})");

        if (!$vehicle->device_id) {
            $device = \App\Models\Device::first();
            if ($device) {
                $vehicle->device_id = $device->id;
                $vehicle->save();
                $this->command->info("Assigned Device ID: {$device->id} to vehicle.");
            } else {
                $this->command->error("No devices found in DB. Please run DemoDataSeeder.");
                return;
            }
        }

        $this->command->info("Using Device ID: {$vehicle->device_id}");
        
        // 1. Update recent GPS data with diagnostic metrics
        // Get last 100 GPS points or create some if none exist
        $recentData = GpsData::where('vehicle_id', $vehicle->id)
            ->latest('gps_time')
            ->limit(100)
            ->get();
            
        if ($recentData->isEmpty()) {
             // Create some dummy GPS data if needed
             // For now, let's assume gps_data exists or we just create a few new points
             $startDate = Carbon::now()->subHours(24);
             for ($i = 0; $i < 50; $i++) {
                 GpsData::create([
                     'tenant_id' => $vehicle->tenant_id,
                     'device_id' => $vehicle->device_id, // Ensure vehicle has device_id
                     'vehicle_id' => $vehicle->id,
                     'latitude' => 23.8103 + ($i * 0.001),
                     'longitude' => 90.4125 + ($i * 0.001),
                     'speed' => rand(0, 60),
                     'heading' => rand(0, 360),
                     'gps_time' => $startDate->copy()->addMinutes($i * 30),
                     'server_time' => $startDate->copy()->addMinutes($i * 30),
                     'ignition' => true,
                     'rpm' => rand(800, 2500),
                     'battery_voltage' => rand(125, 142) / 10,
                     'coolant_temp' => rand(85, 95),
                     'engine_load' => rand(20, 60),
                     'fuel_level' => rand(40, 90),
                 ]);
             }
        } else {
            foreach ($recentData as $data) {
                $data->update([
                    'coolant_temp' => rand(85, 105),
                    'engine_load' => rand(20, 70),
                    'fuel_level' => rand(30, 80),
                    // ensuring battery and rpm are set
                    'battery_voltage' => $data->battery_voltage ?? rand(125, 142) / 10,
                    'rpm' => $data->rpm ?? rand(800, 3000),
                ]);
            }
        }

        // 2. Create some diagnostic codes (DTCs)
        // Active code
        DiagnosticCode::create([
            'tenant_id' => $vehicle->tenant_id,
            'device_id' => $vehicle->device_id,
            'vehicle_id' => $vehicle->id,
            'code' => 'P0128',
            'description' => 'Coolant Thermostat (Coolant Temperature Below Thermostat Regulating Temperature)',
            'severity' => 'medium',
            'detected_at' => now()->subHours(2),
        ]);

        // Cleared code
        DiagnosticCode::create([
            'tenant_id' => $vehicle->tenant_id,
            'device_id' => $vehicle->device_id,
            'vehicle_id' => $vehicle->id,
            'code' => 'P0300',
            'description' => 'Random/Multiple Cylinder Misfire Detected',
            'severity' => 'high',
            'detected_at' => now()->subDays(2),
            'cleared_at' => now()->subDays(1),
            'cleared_by' => 1, // Assuming admin user ID 1
            'notes' => 'Replaced spark plugs',
        ]);
        
        // Critical code (active)
        DiagnosticCode::create([
            'tenant_id' => $vehicle->tenant_id,
            'device_id' => $vehicle->device_id,
            'vehicle_id' => $vehicle->id,
            'code' => 'P0217',
            'description' => 'Engine Coolant Over Temperature Condition',
            'severity' => 'critical',
            'detected_at' => now()->subMinutes(30),
        ]);

        $this->command->info('Diagnostic data seeded successfully.');
    }
}
