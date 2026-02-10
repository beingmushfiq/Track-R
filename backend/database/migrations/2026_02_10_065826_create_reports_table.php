<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // trip, idle, event, summary, etc.
            $table->json('parameters')->nullable(); // { vehicle_ids: [], date_range: [] }
            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_frequency')->nullable(); // daily, weekly, monthly
            $table->json('recipients')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('is_scheduled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
