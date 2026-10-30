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
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('objective');
            $table->timestamp('deadline')->nullable();
            $table->foreignId('type_id')->nullable()->constrained('mission_types')->onDelete('set null');
            $table->enum('priority', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->foreignId('leader_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->foreignId('map_id')->nullable()->constrained('maps')->onDelete('set null');
            $table->enum('statement', [
                'created',
                'waiting_start',
                'in_progress',
                'aborted',
                'waiting_validation',
                'completed'
            ])->default('created');
            $table->enum('result', ['success', 'failed'])->nullable();
            $table->string('code')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
