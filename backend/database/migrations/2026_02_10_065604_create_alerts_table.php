<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_model_id')->constrained()->onDelete('restrict');
            $table->foreignId('alert_rule_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', [
                'overspeed',
                'geofence_enter',
                'geofence_exit',
                'ignition_on',
                'ignition_off',
                'low_battery',
                'sos',
                'power_cut',
                'harsh_braking',
                'harsh_acceleration',
                'idle_too_long',
                'device_offline',
                'custom'
            ]);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title', 200);
            $table->text('message');
            $table->json('data')->nullable()->comment('Alert-specific data');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('address', 500)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('triggered_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('vehicle_id');
            $table->index('device_id');
            $table->index('type');
            $table->index('severity');
            $table->index('is_read');
            $table->index('triggered_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
