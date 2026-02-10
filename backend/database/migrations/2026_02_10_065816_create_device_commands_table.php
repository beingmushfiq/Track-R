<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type', 50); // engine_stop, engine_resume, custom, etc.
            $table->text('command');
            $table->string('status', 20)->default('pending'); // pending, sent, delivered, executed, failed
            $table->json('response')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('device_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};
