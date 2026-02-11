<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\AlertNotification;
use App\Notifications\DeviceOfflineNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Models\VehicleType;
use App\Models\DeviceModel;
use App\Channels\TwilioChannel;
use App\Channels\WhatsAppChannel;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $user;
    protected $device;
    protected $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Test Tenant', 
            'domain' => 'test.com',
            'type' => 'company',
            'slug' => 'test-tenant',
            'email' => 'tenant@test.com',
        ]);
        
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'phone' => '+1234567890',
        ]);
        
        $vehicleType = VehicleType::create([
            'name' => 'Car',
            'icon' => 'car',
        ]);

        $this->vehicle = Vehicle::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Test Vehicle',
            'plate_number' => 'TEST-123',
        ]);

        $deviceModel = DeviceModel::create([
            'manufacturer' => 'Generic',
            'model' => 'GT06',
            'protocol' => 'gt06',
        ]);

        $this->device = Device::create([
            'tenant_id' => $this->tenant->id,
            'vehicle_id' => $this->vehicle->id,
            'device_model_id' => $deviceModel->id,
            'imei' => '123456789012345',
            'unique_id' => 'device-123',
            'name' => 'Test Device',
        ]);
    }

    public function test_alert_notification_is_sent_via_correct_channels()
    {
        Notification::fake();

        $alert = Alert::create([
            'tenant_id' => $this->tenant->id,
            'device_id' => $this->device->id,
            'vehicle_id' => $this->vehicle->id,
            'device_model_id' => $this->device->device_model_id,
            'type' => 'overspeed',
            'title' => 'Speed Alert',
            'message' => 'Vehicle exceeded speed limit',
            'latitude' => 0,
            'longitude' => 0,
        ]);

        $service = new NotificationService();
        $service->sendAlertNotification($alert);

        Notification::assertSentTo(
            [$this->user],
            AlertNotification::class,
            function ($notification, $channels) {
                // Check if channels contains the class names
                // Laravel 8+ notification channels are usually strings or class names
                return in_array('mail', $channels) && 
                       in_array('database', $channels) &&
                       in_array(TwilioChannel::class, $channels) &&
                       in_array(WhatsAppChannel::class, $channels);
            }
        );
    }

    public function test_device_offline_notification_is_sent()
    {
        Notification::fake();

        $service = new NotificationService();
        $service->sendDeviceOfflineNotification($this->device);

        Notification::assertSentTo(
            [$this->user],
            DeviceOfflineNotification::class
        );
    }
}
