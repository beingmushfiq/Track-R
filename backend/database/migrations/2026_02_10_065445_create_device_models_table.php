<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_models', function (Blueprint $table) {
            $table->id();
            $table->string('manufacturer', 100);
            $table->string('model', 100);
            $table->string('protocol', 50)->default('Generic');
            $table->integer('default_port')->nullable();
            $table->json('features')->nullable()->comment('Supported features: fuel_sensor, temperature, camera, etc.');
            $table->json('configuration')->nullable()->comment('Default configuration settings');
            $table->timestamps();

            $table->unique(['manufacturer', 'model']);
            $table->index('protocol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_models');
    }
};
