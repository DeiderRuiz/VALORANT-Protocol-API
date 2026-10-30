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
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', [
                'mission_created_deadline_warning',
                'mission_waiting_start_deadline_warning',
                'mission_validation_warning',
                'mission_aborted',
                'mission_assigned',
                'mission_unassigned',
                'mission_updated',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', [
                'mission_deadline_warning',
                'mission_validation_warning',
                'mission_aborted',
                'mission_assigned',
                'mission_unassigned',
                'mission_updated',
            ])->change();
        });
    }
};
