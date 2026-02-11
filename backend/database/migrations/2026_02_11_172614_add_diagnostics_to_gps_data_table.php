<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gps_data', function (Blueprint $table) {
            // Add diagnostic fields if they don't exist
            if (!Schema::hasColumn('gps_data', 'coolant_temp')) {
                $table->decimal('coolant_temp', 5, 2)->nullable()->comment('Engine coolant temperature in Celsius');
            }
            if (!Schema::hasColumn('gps_data', 'engine_load')) {
                $table->decimal('engine_load', 5, 2)->nullable()->comment('Engine load percentage (0-100)');
            }
            if (!Schema::hasColumn('gps_data', 'fuel_level')) {
                $table->decimal('fuel_level', 5, 2)->nullable()->comment('Fuel level percentage (0-100)');
            }
            // Note: rpm and battery_voltage already added in previous migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_data', function (Blueprint $table) {
            $table->dropColumn(['coolant_temp', 'engine_load', 'fuel_level']);
        });
    }
};
