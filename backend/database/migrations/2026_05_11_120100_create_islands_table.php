<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('islands', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('atoll_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['atoll_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('islands');
    }
};
