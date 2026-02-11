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
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'engine_locked')) {
                $table->boolean('engine_locked')->default(false);
            }
            if (!Schema::hasColumn('devices', 'last_command_at')) {
                $table->timestamp('last_command_at')->nullable();
            }
            if (!Schema::hasColumn('devices', 'last_command_type')) {
                $table->string('last_command_type')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['engine_locked', 'last_command_at', 'last_command_type']);
        });
    }
};
