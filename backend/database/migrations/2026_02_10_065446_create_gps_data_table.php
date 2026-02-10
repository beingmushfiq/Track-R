<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gps_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('altitude', 8, 2)->nullable()->comment('Meters');
            $table->decimal('speed', 8, 2)->nullable()->comment('km/h');
            $table->smallInteger('heading')->nullable()->comment('0-360 degrees');
            $table->tinyInteger('satellites')->nullable();
            $table->decimal('hdop', 5, 2)->nullable()->comment('Horizontal Dilution of Precision');
            $table->decimal('odometer', 12, 2)->nullable()->comment('Total distance in km');
            $table->decimal('fuel_level', 5, 2)->nullable()->comment('Percentage');
            $table->decimal('battery_voltage', 5, 2)->nullable()->comment('Volts');
            $table->tinyInteger('gsm_signal')->nullable()->comment('0-100%');
            $table->boolean('ignition')->nullable();
            $table->boolean('gps_valid')->default(true);
            $table->string('address', 500)->nullable()->comment('Reverse geocoded address');
            $table->json('raw_data')->nullable()->comment('Original packet data');
            $table->timestamp('gps_time');
            $table->timestamp('server_time');
            $table->timestamps();

            // Indexes for performance
            $table->index('device_id');
            $table->index('vehicle_id');
            $table->index('gps_time');
            $table->index('server_time');
            $table->index(['device_id', 'gps_time']);
            $table->index(['vehicle_id', 'gps_time']);
            $table->index(['latitude', 'longitude'], 'location_index');
        });

        // Note: Partitioning by month will be handled via raw SQL after migration
        // ALTER TABLE gps_data PARTITION BY RANGE (YEAR(gps_time) * 100 + MONTH(gps_time))
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_data');
    }
};
