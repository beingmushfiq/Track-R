<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('device_model_id')->constrained()->onDelete('restrict');
            $table->string('imei', 20)->unique();
            $table->string('sim_number', 20)->nullable();
            $table->string('sim_provider', 50)->nullable();
            $table->date('installation_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->json('configuration')->nullable()->comment('Device-specific settings');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_communication')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('vehicle_id');
            $table->index('device_model_id');
            $table->index('status');
            $table->index('is_online');
            $table->index('last_communication');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
