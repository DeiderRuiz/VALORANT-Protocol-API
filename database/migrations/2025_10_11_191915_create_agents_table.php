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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('bio');
            $table->string('profile_photo', 2048);
            $table->string('agent_photo', 2048);
            $table->foreignId('nationality_id')->nullable()->constrained('nationalities')->onDelete('set null');
            $table->foreignId('class_id')->nullable()->constrained('agent_classes')->onDelete('set null');
            $table->boolean('available')->default(false);
            $table->boolean('is_draft')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
