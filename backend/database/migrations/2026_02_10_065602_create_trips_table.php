<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->decimal('start_latitude', 10, 7);
            $table->decimal('start_longitude', 10, 7);
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();
            $table->string('start_address', 500)->nullable();
            $table->string('end_address', 500)->nullable();
            $table->decimal('start_odometer', 10, 2)->nullable();
            $table->decimal('end_odometer', 10, 2)->nullable();
            $table->decimal('start_fuel_level', 8, 2)->nullable();
            $table->decimal('end_fuel_level', 8, 2)->nullable();
            $table->decimal('distance', 10, 2)->default(0)->comment('km');
            $table->integer('duration')->default(0)->comment('seconds');
            $table->decimal('max_speed', 8, 2)->nullable()->comment('km/h');
            $table->decimal('avg_speed', 8, 2)->nullable()->comment('km/h');
            $table->decimal('fuel_consumed', 8, 2)->nullable()->comment('liters');
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('device_id');
            $table->index('start_time');
            $table->index('end_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
