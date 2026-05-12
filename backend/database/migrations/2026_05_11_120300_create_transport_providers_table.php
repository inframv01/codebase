<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('type');
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_providers');
    }
};
