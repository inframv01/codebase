<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('capacity_kg', 10, 2)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('boat_island_group', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('boat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('island_group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['boat_id', 'island_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boat_island_group');
        Schema::dropIfExists('boats');
    }
};
