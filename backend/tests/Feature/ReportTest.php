<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\Tenant;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

    public function test_can_generate_trip_report()
    {
        // Create trip
        Trip::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subDays(2)->addHour(),
            'start_latitude' => 40.0,
            'start_longitude' => -74.0,
            'end_latitude' => 40.1,
            'end_longitude' => -74.1,
            'distance' => 50,
            'duration' => 3600,
            'max_speed' => 80,
            'avg_speed' => 50,
            'fuel_consumed' => 5,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/trips?start_date=' . Carbon::now()->subWeek()->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.total_trips', 1)
            ->assertJsonPath('data.summary.total_distance', 50);
    }

    public function test_can_generate_distance_report()
    {
        // Create trip
        Trip::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addHours(2),
            'start_latitude' => 40.0,
            'start_longitude' => -74.0,
            'end_latitude' => 40.2,
            'end_longitude' => -74.2,
            'distance' => 120.5,
            'duration' => 7200,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/distance?start_date=' . Carbon::now()->subWeek()->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.total_distance', 120.5)
            ->assertJsonPath('data.vehicles.0.vehicle_id', $this->vehicle->id);
    }

    public function test_can_generate_fuel_report()
    {
        // Create trip with fuel
        Trip::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addHour(),
            'start_latitude' => 40.0,
            'start_longitude' => -74.0,
            'distance' => 100,
            'duration' => 3600,
            'fuel_consumed' => 10, // 10 km/L
            'start_fuel_level' => 50,
            'end_fuel_level' => 40,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/fuel?start_date=' . Carbon::now()->subWeek()->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.total_fuel_consumed', 10)
            ->assertJsonPath('data.summary.avg_fuel_efficiency', 10);
    }

    public function test_can_generate_alert_report()
    {
        // Create Alert Rule
        $rule = AlertRule::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Speed Limit',
            'type' => 'overspeed',
            'conditions' => ['speed' => 100],
            'actions' => ['notification'],
            'is_active' => true,
        ]);

        // Create Alert
        Alert::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'device_model_id' => $this->device->device_model_id,
            'alert_rule_id' => $rule->id,
            'type' => 'overspeed',
            'severity' => 'critical',
            'title' => 'Speed Alert',
            'message' => 'Vehicle Exceeded Speed Limit',
            'latitude' => 40.0,
            'longitude' => -74.0,
            'triggered_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/alerts?start_date=' . Carbon::now()->subWeek()->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.total_alerts', 1)
            ->assertJsonCount(1, 'data.by_type')
            ->assertJsonCount(1, 'data.by_vehicle');
    }

    public function test_can_generate_daily_activity_report()
    {
        // Create trip today
        Trip::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_id' => $this->device->id,
            'start_time' => Carbon::now()->startOfDay()->addHour(),
            'end_time' => Carbon::now()->startOfDay()->addHours(2),
            'start_latitude' => 40.0,
            'start_longitude' => -74.0,
            'distance' => 60,
            'duration' => 3600,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/daily-activity?date=' . Carbon::now()->toDateString());

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.total_trips', 1)
            ->assertJsonPath('data.summary.active_vehicles', 1)
            ->assertJsonPath('data.date', Carbon::now()->format('Y-m-d'));
    }
}
