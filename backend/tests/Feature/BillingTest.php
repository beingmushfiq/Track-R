<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $user;
    protected $plan;

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

        // Create Admin User
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Create Plan
        $this->plan = Plan::create([
            'name' => 'Pro Plan',
            'slug' => 'pro-plan',
            'price' => 29.99,
            'billing_cycle' => 'monthly',
            'max_vehicles' => 10,
            'max_users' => 5,
            'is_active' => true,
        ]);
    }

    public function test_can_list_available_plans()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/billing/plans');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'pro-plan');
    }

    public function test_tenant_can_subscribe_to_plan()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/billing/subscribe', [
                'plan_id' => $this->plan->id,
                'payment_method' => 'credit_card',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'active');

        // Verify database
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
        ]);

        // Verify invoice generated
        $this->assertDatabaseHas('invoices', [
            'tenant_id' => $this->tenant->id,
            'amount' => 29.99,
            'status' => 'unpaid',
        ]);
    }

    public function test_can_get_current_subscription()
    {
        // First subscribe
        $this->actingAs($this->user)
            ->postJson('/api/billing/subscribe', [
                'plan_id' => $this->plan->id,
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/billing/subscription');

        $response->assertStatus(200)
            ->assertJsonPath('data.plan.slug', 'pro-plan');
    }

    public function test_tenant_can_cancel_subscription()
    {
        // Subscribe first
        $this->actingAs($this->user)
            ->postJson('/api/billing/subscribe', [
                'plan_id' => $this->plan->id,
            ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/billing/cancel');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Subscription cancelled successfully.']);

        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $this->tenant->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_can_list_invoices()
    {
        // Subscribe to generate invoice
        $this->actingAs($this->user)
            ->postJson('/api/billing/subscribe', [
                'plan_id' => $this->plan->id,
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/billing/invoices');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.amount', '29.99');
    }
}
