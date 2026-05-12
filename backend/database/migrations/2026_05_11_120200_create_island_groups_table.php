<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('island_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('island_group_island', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('island_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('island_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['island_group_id', 'island_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('island_group_island');
        Schema::dropIfExists('island_groups');
    }
};
