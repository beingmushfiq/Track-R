<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address', 500)->nullable();
            $table->integer('duration')->default(0)->comment('seconds');
            $table->boolean('engine_off')->default(false);
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('device_id');
            $table->index('trip_id');
            $table->index('start_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stops');
    }
};
