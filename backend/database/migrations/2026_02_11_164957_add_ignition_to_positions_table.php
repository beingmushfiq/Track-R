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
            // Note: ignition and battery_voltage already exist in gps_data table
            // Only adding panic_button and rpm which are new
            if (!Schema::hasColumn('gps_data', 'panic_button')) {
                $table->boolean('panic_button')->default(false);
            }
            if (!Schema::hasColumn('gps_data', 'rpm')) {
                $table->integer('rpm')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_data', function (Blueprint $table) {
            $table->dropColumn(['panic_button', 'rpm']);
        });
    }
};
