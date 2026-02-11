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
        Schema::create('report_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('report_type', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->enum('delivery_method', ['email', 'sms'])->default('email');
            $table->time('delivery_time')->default('08:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'report_type']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_subscriptions');
    }
};
