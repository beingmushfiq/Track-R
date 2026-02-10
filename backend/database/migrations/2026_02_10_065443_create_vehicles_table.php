<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('vehicle_group_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name', 100);
            $table->string('registration_number', 50)->nullable();
            $table->string('vin', 50)->nullable()->comment('Vehicle Identification Number');
            $table->string('make', 50)->nullable();
            $table->string('model', 50)->nullable();
            $table->year('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->decimal('fuel_capacity', 8, 2)->nullable()->comment('Liters');
            $table->decimal('fuel_consumption', 8, 2)->nullable()->comment('L/100km');
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_phone', 50)->nullable();
            $table->string('photo')->nullable();
            $table->json('custom_fields')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('vehicle_type_id');
            $table->index('vehicle_group_id');
            $table->index('status');
            $table->unique(['tenant_id', 'registration_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
