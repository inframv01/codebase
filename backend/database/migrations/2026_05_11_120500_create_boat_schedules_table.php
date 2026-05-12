<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boat_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('boat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('origin_island_id')->constrained('islands')->cascadeOnDelete();
            $table->foreignId('destination_island_id')->constrained('islands')->cascadeOnDelete();
            $table->timestamp('departs_at');
            $table->timestamp('arrives_at');
            $table->string('status')->default('scheduled')->index();
            $table->decimal('capacity_remaining_kg', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_schedules');
    }
};
