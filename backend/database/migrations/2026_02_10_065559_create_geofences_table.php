<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('type', ['circle', 'polygon'])->default('circle');
            $table->decimal('center_lat', 10, 7)->nullable()->comment('For circle type');
            $table->decimal('center_lng', 10, 7)->nullable()->comment('For circle type');
            $table->integer('radius')->nullable()->comment('Meters, for circle type');
            $table->json('coordinates')->nullable()->comment('Array of lat/lng for polygon');
            $table->string('color', 7)->default('#EF4444');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};
