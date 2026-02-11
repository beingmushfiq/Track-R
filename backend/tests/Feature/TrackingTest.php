<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\GpsData;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $user;
    protected $vehicle;
    protected $device;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test.com',
            'type' => 'company',
            'slug' => 'test-tenant',
            'email' => 'tenant@test.com',
        ]);

        // Create User
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create Vehicle Type
        $vehicleType = VehicleType::create([
            'name' => 'Car',
            'icon' => 'car',
        ]);

        // Create Vehicle
        $this->vehicle = Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Test Vehicle',
            'registration_number' => 'ABC-123',
            'status' => 'active',
        ]);

        // Create Device Model
        $deviceModel = DeviceModel::create([
            'manufacturer' => 'TestBrand',
            'model' => 'ModelX',
            'protocol' => 'gt06',
        ]);

        // Create Device
        $this->device = Device::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_model_id' => $deviceModel->id,
            'imei' => '123456789012345',
            'unique_id' => 'device-123',
            'is_online' => true,
        ]);
    }

    public function test_can_get_live_tracking_data()
    {
        // Add some GPS data
        GpsData::create([
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'tenant_id' => $this->tenant->id,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'speed' => 50,
            'heading' => 90,
            'altitude' => 10,
            'gps_valid' => true,
            'gps_time' => Carbon::now(),
            'server_time' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/tracking/live');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.vehicle_id', $this->vehicle->id)
            ->assertJsonPath('data.0.position.latitude', 40.7128);
    }

    public function test_can_get_vehicle_last_position()
    {
        // Add GPS data
        GpsData::create([
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'tenant_id' => $this->tenant->id,
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'speed' => 60,
            'heading' => 180,
            'altitude' => 15,
            'gps_valid' => true,
            'gps_time' => Carbon::now(),
            'server_time' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tracking/vehicle/{$this->vehicle->id}/last-position");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.position.latitude', 34.0522);
    }

    public function test_can_get_playback_data()
    {
        $now = Carbon::now();
        
        // Add historical data
        // ... (keep creating data) ...
        GpsData::create([
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'tenant_id' => $this->tenant->id,
            'latitude' => 40.0000,
            'longitude' => -70.0000,
            'speed' => 40,
            'heading' => 90,
            'gps_valid' => true,
            'gps_time' => $now->copy()->subHours(2),
            'server_time' => $now->copy()->subHours(2),
        ]);

        GpsData::create([
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'tenant_id' => $this->tenant->id,
            'latitude' => 40.0100,
            'longitude' => -70.0100,
            'speed' => 45,
            'heading' => 90,
            'gps_valid' => true,
            'gps_time' => $now->copy()->subHour(),
            'server_time' => $now->copy()->subHour(),
        ]);

        $start = $now->copy()->subHours(3)->toIso8601String();
        $end = $now->copy()->addHour()->toIso8601String();
        
        $query = http_build_query(['start' => $start, 'end' => $end]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tracking/vehicle/{$this->vehicle->id}/playback?{$query}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.route');
    }

    public function test_can_get_route_statistics()
    {
        $now = Carbon::now();
        
        // Add historical data
        // Point A
        GpsData::create([
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'tenant_id' => $this->tenant->id,
            'latitude' => 40.0000,
            'longitude' => -74.0000,
            'speed' => 60,
            'heading' => 90,
            'gps_valid' => true,
            'gps_time' => $now->copy()->subHour(),
            'server_time' => $now->copy()->subHour(),
        ]);

        // Point B (approx 11km away)
        GpsData::create([
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'tenant_id' => $this->tenant->id,
            'latitude' => 40.1000,
            'longitude' => -74.0000,
            'speed' => 80,
            'heading' => 0,
            'gps_valid' => true,
            'gps_time' => $now->copy(),
            'server_time' => $now->copy(),
        ]);

        $start = $now->copy()->subHours(2)->toIso8601String();
        $end = $now->copy()->addHour()->toIso8601String();

        $query = http_build_query(['start' => $start, 'end' => $end]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tracking/vehicle/{$this->vehicle->id}/route-stats?{$query}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.statistics.max_speed', 80);
            
        // approximate distance check (greater than 0)
        $this->assertTrue($response->json('data.statistics.total_distance') > 0);
    }
}
