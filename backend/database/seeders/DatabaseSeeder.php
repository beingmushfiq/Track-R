<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleGroup;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Tenant
        $tenant = Tenant::create([
            'name' => 'Demo Transport Co.',
            'type' => 'company',
            'slug' => 'demo-transport',
            'email' => 'admin@demotransport.com',
            'is_active' => true,
        ]);

        // 2. Create Admin User
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@demotransport.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        // 3. Create Vehicle Type
        $type = VehicleType::create([
            'name' => 'Truck',
            'icon' => 'truck',
            'description' => 'Heavy goods vehicle',
        ]);

        // 4. Create Vehicle Group
        $group = VehicleGroup::create([
            'tenant_id' => $tenant->id,
            'name' => 'North Fleet',
            'color' => '#FF0000',
        ]);

        // 5. Create Vehicle
        $vehicle = Vehicle::create([
            'tenant_id' => $tenant->id,
            'vehicle_type_id' => $type->id,
            'vehicle_group_id' => $group->id,
            'name' => 'Truck 001',
            'registration_number' => 'TRK-001',
            'vin' => '123456789ABC',
            'status' => 'active',
        ]);

        // 6. Create Device Model
        $model = DeviceModel::create([
            'manufacturer' => 'Teltonika',
            'model' => 'FMB920',
            'protocol' => 'Code8',
        ]);

        // 7. Create Device
        Device::create([
            'tenant_id' => $tenant->id,
            'vehicle_id' => $vehicle->id,
            'device_model_id' => $model->id,
            'imei' => '123456789012345',
            'status' => 'active',
            'is_online' => false,
        ]);

        $this->command->info('Test data seeded successfully! IMEI: 123456789012345');
    }
}
