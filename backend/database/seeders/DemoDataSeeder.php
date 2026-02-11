<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\VehicleGroup;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\Geofence;
use App\Models\AlertRule;
use App\Models\GpsData;
use App\Models\Trip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating demo data...');

        // Create Roles and Permissions
        $this->createRolesAndPermissions();

        // Create Tenants
        $tenant1 = $this->createTenant('Dhaka Transport Ltd', 'dhaka-transport');
        $tenant2 = $this->createTenant('Chittagong Logistics', 'chittagong-logistics');

        // Create Users for each tenant
        $this->createUsers($tenant1);
        $this->createUsers($tenant2);

        // Create Vehicle Types
        $vehicleTypes = $this->createVehicleTypes();

        // Create Vehicles and Devices for each tenant
        $this->createVehiclesAndDevices($tenant1, $vehicleTypes);
        $this->createVehiclesAndDevices($tenant2, $vehicleTypes);

        // Create Geofences
        $this->createGeofences($tenant1);
        $this->createGeofences($tenant2);

        // Create Alert Rules
        $this->createAlertRules($tenant1);
        $this->createAlertRules($tenant2);

        // Create Sample GPS Data and Trips
        $this->createSampleGpsData($tenant1);
        $this->createSampleGpsData($tenant2);

        $this->command->info('Demo data created successfully!');
    }

    protected function createRolesAndPermissions(): void
    {
        $this->command->info('Creating roles and permissions...');

        // Create permissions
        $permissions = [
            'view_vehicles',
            'create_vehicles',
            'edit_vehicles',
            'delete_vehicles',
            'view_devices',
            'create_devices',
            'edit_devices',
            'delete_devices',
            'view_tracking',
            'view_reports',
            'create_reports',
            'view_geofences',
            'create_geofences',
            'edit_geofences',
            'delete_geofences',
            'view_alerts',
            'create_alerts',
            'manage_users',
            // Panic Event Permissions
            'view panic events',
            'create panic events',
            'resolve panic events',
            'delete panic events',
            // Device Command Permissions
            'send device commands',
            'lock engine',
            'unlock engine',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->syncPermissions([
            'view_vehicles', 'create_vehicles', 'edit_vehicles',
            'view_devices', 'create_devices', 'edit_devices',
            'view_tracking', 'view_reports', 'create_reports',
            'view_geofences', 'create_geofences', 'edit_geofences',
            'view_alerts', 'create_alerts',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'Viewer']);
        $viewer->syncPermissions([
            'view_vehicles', 'view_devices', 'view_tracking', 'view_reports', 'view_geofences', 'view_alerts',
        ]);
    }

    protected function createTenant(string $name, string $slug): Tenant
    {
        $this->command->info("Creating tenant: {$name}");

        return Tenant::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'email' => "{$slug}@example.com",
                'phone' => '+880' . rand(1000000000, 9999999999),
                'address' => 'Dhaka, Bangladesh',
                'is_active' => true,
            ]
        );
    }

    protected function createUsers(Tenant $tenant): void
    {
        $this->command->info("Creating users for {$tenant->name}...");

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => "admin@{$tenant->slug}.com"],
            [
                'name' => 'Admin User',
                'tenant_id' => $tenant->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Admin');

        // Manager user
        $manager = User::firstOrCreate(
            ['email' => "manager@{$tenant->slug}.com"],
            [
                'name' => 'Manager User',
                'tenant_id' => $tenant->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('Manager');

        // Viewer user
        $viewer = User::firstOrCreate(
            ['email' => "viewer@{$tenant->slug}.com"],
            [
                'name' => 'Viewer User',
                'tenant_id' => $tenant->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $viewer->assignRole('Viewer');
    }

    protected function createVehicleTypes(): array
    {
        $this->command->info('Creating vehicle types...');

        $types = ['Truck', 'Van', 'Car', 'Bus', 'Motorcycle'];
        $vehicleTypes = [];

        foreach ($types as $type) {
            $vehicleTypes[] = VehicleType::firstOrCreate(['name' => $type]);
        }

        return $vehicleTypes;
    }

    protected function createVehiclesAndDevices(Tenant $tenant, array $vehicleTypes): void
    {
        $this->command->info("Creating vehicles and devices for {$tenant->name}...");

        // Create device models
        $deviceModels = [
            DeviceModel::firstOrCreate(
                ['manufacturer' => 'Queclink', 'model' => 'GT06'],
                ['protocol' => 'GT06', 'default_port' => 5023]
            ),
            DeviceModel::firstOrCreate(
                ['manufacturer' => 'Huabao', 'model' => 'HT02'],
                ['protocol' => 'HT02', 'default_port' => 5024]
            ),
            DeviceModel::firstOrCreate(
                ['manufacturer' => 'Concox', 'model' => 'GT06N'],
                ['protocol' => 'Concox', 'default_port' => 5025]
            ),
        ];

        // Create vehicle group
        $group = VehicleGroup::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Fleet A',
            ],
            [
                'description' => 'Primary fleet vehicles',
            ]
        );

        // Create 10 vehicles with devices
        for ($i = 1; $i <= 10; $i++) {
            $vehicleType = $vehicleTypes[array_rand($vehicleTypes)];
            $deviceModel = $deviceModels[array_rand($deviceModels)];

            $vehicle = Vehicle::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'registration_number' => "DHAKA-{$tenant->id}-{$i}",
                ],
                [
                    'name' => "{$vehicleType->name} {$i}",
                    'vehicle_type_id' => $vehicleType->id,
                    'vehicle_group_id' => $group->id,
                    'make' => ['Toyota', 'Honda', 'Tata', 'Ashok Leyland'][array_rand(['Toyota', 'Honda', 'Tata', 'Ashok Leyland'])],
                    'model' => 'Model ' . chr(65 + $i),
                    'year' => rand(2018, 2024),
                    'vin' => 'VIN' . strtoupper(bin2hex(random_bytes(8))),
                    'color' => ['White', 'Black', 'Red', 'Blue'][array_rand(['White', 'Black', 'Red', 'Blue'])],
                    'fuel_capacity' => rand(40, 100),
                    'is_active' => true,
                ]
            );

            // Create device for vehicle
            $imei = '86' . str_pad($tenant->id, 2, '0', STR_PAD_LEFT) . str_pad($i, 11, '0', STR_PAD_LEFT);

            Device::firstOrCreate(
                [
                    'imei' => $imei,
                ],
                [
                    'tenant_id' => $tenant->id,
                    'vehicle_id' => $vehicle->id,
                    'device_model_id' => $deviceModel->id,
                    'name' => "Device {$i}",
                    'phone_number' => '+880' . rand(1000000000, 9999999999),
                    'sim_number' => '880' . rand(1000000000, 9999999999),
                    'is_active' => true,
                    'is_online' => rand(0, 1) == 1,
                    'last_communication' => Carbon::now()->subMinutes(rand(1, 60)),
                ]
            );
        }
    }

    protected function createGeofences(Tenant $tenant): void
    {
        $this->command->info("Creating geofences for {$tenant->name}...");

        // Dhaka city center (circular geofence)
        Geofence::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Dhaka City Center',
            ],
            [
                'type' => 'circle',
                'center_lat' => 23.8103,
                'center_lng' => 90.4125,
                'radius' => 5000, // 5km
                'is_active' => true,
            ]
        );

        // Chittagong port area (circular geofence)
        Geofence::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Chittagong Port',
            ],
            [
                'type' => 'circle',
                'center_lat' => 22.3569,
                'center_lng' => 91.7832,
                'radius' => 3000, // 3km
                'is_active' => true,
            ]
        );

        // Warehouse area (polygon geofence)
        Geofence::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Warehouse Zone',
            ],
            [
                'type' => 'polygon',
                'coordinates' => [
                    ['lat' => 23.8000, 'lng' => 90.4000],
                    ['lat' => 23.8100, 'lng' => 90.4000],
                    ['lat' => 23.8100, 'lng' => 90.4200],
                    ['lat' => 23.8000, 'lng' => 90.4200],
                ],
                'is_active' => true,
            ]
        );
    }

    protected function createAlertRules(Tenant $tenant): void
    {
        $this->command->info("Creating alert rules for {$tenant->name}...");

        $vehicles = Vehicle::where('tenant_id', $tenant->id)->take(5)->get();

        // Overspeed alert
        $overspeedRule = AlertRule::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Overspeed Alert',
            ],
            [
                'type' => 'speed',
                'condition' => 'greater_than',
                'value' => 80, // 80 km/h
                'is_active' => true,
            ]
        );
        $overspeedRule->vehicles()->sync($vehicles->pluck('id'));

        // Idle alert
        $idleRule = AlertRule::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Idle Time Alert',
            ],
            [
                'type' => 'idle',
                'condition' => 'greater_than',
                'value' => 600, // 10 minutes
                'is_active' => true,
            ]
        );
        $idleRule->vehicles()->sync($vehicles->pluck('id'));

        // Device offline alert
        AlertRule::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Device Offline',
            ],
            [
                'type' => 'offline',
                'condition' => 'greater_than',
                'value' => 1800, // 30 minutes
                'is_active' => true,
            ]
        );
    }

    protected function createSampleGpsData(Tenant $tenant): void
    {
        $this->command->info("Creating sample GPS data for {$tenant->name}...");

        $devices = Device::where('tenant_id', $tenant->id)->with('vehicle')->get();

        foreach ($devices as $device) {
            // Create GPS data for the last 24 hours
            $startTime = Carbon::now()->subHours(24);
            $currentTime = clone $startTime;

            // Simulate a trip
            $tripStarted = false;
            $tripStartTime = null;
            $baseLatitude = 23.8103 + (rand(-100, 100) / 1000);
            $baseLongitude = 90.4125 + (rand(-100, 100) / 1000);

            while ($currentTime <= Carbon::now()) {
                $ignition = rand(0, 100) > 20; // 80% chance ignition is on
                $speed = $ignition ? rand(0, 100) : 0;

                // Simulate movement
                if ($speed > 5) {
                    $baseLatitude += (rand(-10, 10) / 10000);
                    $baseLongitude += (rand(-10, 10) / 10000);
                }

                GpsData::create([
                    'tenant_id' => $tenant->id,
                    'device_id' => $device->id,
                    'vehicle_id' => $device->vehicle_id,
                    'latitude' => $baseLatitude,
                    'longitude' => $baseLongitude,
                    'altitude' => rand(0, 100),
                    'speed' => $speed,
                    'heading' => rand(0, 360),
                    'satellites' => rand(4, 12),
                    'hdop' => rand(10, 30) / 10,
                    'gps_valid' => true,
                    'ignition' => $ignition,
                    'gps_time' => $currentTime,
                    'server_time' => $currentTime,
                ]);

                // Create trip if conditions met
                if ($ignition && $speed > 5 && !$tripStarted) {
                    Trip::create([
                        'tenant_id' => $tenant->id,
                        'vehicle_id' => $device->vehicle_id,
                        'device_id' => $device->id,
                        'start_time' => $currentTime,
                        'start_latitude' => $baseLatitude,
                        'start_longitude' => $baseLongitude,
                        'distance' => 0,
                        'max_speed' => $speed,
                        'avg_speed' => $speed,
                    ]);
                    $tripStarted = true;
                    $tripStartTime = clone $currentTime;
                }

                // Advance time by 5 minutes
                $currentTime->addMinutes(5);
            }
        }
    }
}
