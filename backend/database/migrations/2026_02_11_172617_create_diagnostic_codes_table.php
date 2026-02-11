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
        Schema::create('diagnostic_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            
            $table->string('code', 10)->comment('DTC code (e.g., P0420, C0035)');
            $table->text('description')->nullable()->comment('Human-readable description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            $table->timestamp('detected_at')->comment('When the code was first detected');
            $table->timestamp('cleared_at')->nullable()->comment('When the code was cleared/resolved');
            $table->foreignId('cleared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable()->comment('Resolution notes');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tenant_id', 'vehicle_id']);
            $table->index('detected_at');
            $table->index('severity');
            $table->index(['cleared_at', 'detected_at']); // For active codes query
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnostic_codes');
    }
};
