<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\GpsData;
use App\Models\Stop;
use App\Models\Tenant;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Services\TripService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripTest extends TestCase
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

    public function test_can_list_trips()
    {
        // Create a trip
        Trip::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->subHour(),
            'distance' => 50.5,
            'duration' => 3600,
            'start_latitude' => 40.7128,
            'start_longitude' => -74.0060,
            'end_latitude' => 40.7306,
            'end_longitude' => -73.9352,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/trips');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_can_get_trip_details()
    {
        $trip = Trip::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->subHour(),
            'distance' => 50.5,
            'duration' => 3600,
            'start_latitude' => 40.7128,
            'start_longitude' => -74.0060,
            'end_latitude' => 40.7306,
            'end_longitude' => -73.9352,
        ]);

        // Add some GPS data for the route
        GpsData::create([
            'tenant_id' => $this->tenant->id,
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 40.7200,
            'longitude' => -74.0000,
            'speed' => 60,
            'gps_time' => Carbon::now()->subMinutes(90),
            'server_time' => Carbon::now()->subMinutes(90),
            'gps_valid' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.trip.id', $trip->id)
            ->assertJsonCount(1, 'data.route');
    }

    public function test_can_get_trip_stops()
    {
        $trip = Trip::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'start_time' => Carbon::now()->subHours(5),
            'end_time' => Carbon::now(),
            'distance' => 100,
            'duration' => 18000,
            'start_latitude' => 40.0,
            'start_longitude' => -74.0,
        ]);

        Stop::create([
            'tenant_id' => $this->tenant->id,
            'trip_id' => $trip->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'latitude' => 40.5,
            'longitude' => -74.0,
            'start_time' => Carbon::now()->subHours(3),
            'end_time' => Carbon::now()->subHours(2),
            'duration' => 3600,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/trips/{$trip->id}/stops");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.stops');
    }

    public function test_trip_service_starts_trip()
    {
        $service = new TripService();

        // 1. Send GPS point: Ignition ON, Moving
        $gpsData = new GpsData([
            'tenant_id' => $this->tenant->id,
            'device_id' => $this->device->id, // Use ID, not object
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 40.0,
            'longitude' => -74.0,
            'speed' => 20, // > 5 km/h
            'ignition' => true,
            'gps_time' => Carbon::now(),
            'odometer' => 1000,
            'fuel_level' => 50,
        ]);
        
        // Ensure GpsData has device relation loaded/accessible if needed, 
        // essentially we are passing it to the service.
        // The service uses $device passed in separately.

        $service->processGpsPoint($this->device, $gpsData);

        $this->assertDatabaseHas('trips', [
            'device_id' => $this->device->id,
            'end_time' => null, // Active trip
            'start_latitude' => 40.0,
        ]);
    }

    public function test_trip_service_ends_trip()
    {
        $service = new TripService();
        $startTime = Carbon::now()->subMinutes(30);

        // Create active trip
        $trip = Trip::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'start_time' => $startTime,
            'start_latitude' => 40.0,
            'start_longitude' => -74.0,
            'start_odometer' => 1000,
            'start_fuel_level' => 50,
            'distance' => 10,
            'max_speed' => 60,
            'avg_speed' => 40,
        ]);

        // Simulate stop for 11 minutes (ignition OFF)
        // First point: stopped
        $stopTime = Carbon::now()->subMinutes(12);
        
        $gpsStopped = new GpsData([
             'tenant_id' => $this->tenant->id,
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 40.1,
            'longitude' => -74.1,
            'speed' => 0,
            'ignition' => false,
            'gps_time' => $stopTime,
        ]);

        $service->processGpsPoint($this->device, $gpsStopped);

        // Trip should still be active, but stop timer simulated (via cache in service, tough to test cache here without mocking, 
        // but let's assume valid flow: checkTripEnd uses cache)
        
        // Actually, checkTripEnd sets cache if not set.
        // We need to verify it *doesn't* end immediately.
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'end_time' => null,
        ]);

        // Now send point 11 minutes later
        $gpsEnd = new GpsData([
             'tenant_id' => $this->tenant->id,
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'latitude' => 40.1,
            'longitude' => -74.1,
            'speed' => 0,
            'ignition' => false, // Still off
            'gps_time' => Carbon::now()->subMinute(), // 11 mins later effectively
            'odometer' => 1010,
            'fuel_level' => 48,
        ]);

        $service->processGpsPoint($this->device, $gpsEnd);

        // Trip should be ended
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
        ]);
        
        $updatedTrip = Trip::find($trip->id);
        $this->assertNotNull($updatedTrip->end_time);
        $this->assertEquals(1010, $updatedTrip->end_odometer);
    }
}
