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
        Schema::table('mission_events', function (Blueprint $table) {
            $table->enum('type', [
                'mission_created',
                'mission_updated',
                'start_check',
                'mission_started',
                'abort_request',
                'abort_approved',
                'abort_rejected',
                'abort',
                'report_submitted',
                'mission_completed '
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_events', function (Blueprint $table) {
            $table->enum('type', [
                'mission_created',
                'start_check',
                'mission_started',
                'abort_request',
                'abort_approved',
                'abort_rejected',
                'abort',
                'report_submitted',
                'mission_completed '
            ])->change();
        });
    }
};
