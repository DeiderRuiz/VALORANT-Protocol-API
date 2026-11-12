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
        Schema::create('mission_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
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
                'mission_completed'
            ]);
            $table->text('description');
            $table->text('details')->nullable();
            // Relación auto-referenciada
            $table->foreignId('related_event_id')->nullable()->constrained('mission_events')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_events');
    }
};
