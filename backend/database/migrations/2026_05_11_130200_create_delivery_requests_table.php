<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('destination_island_id')->constrained('islands')->cascadeOnDelete();
            $table->foreignId('transport_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('boat_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->index();
            $table->string('current_stage')->nullable();
            $table->bigInteger('fixed_cost_cents');
            $table->bigInteger('variable_cost_cents')->nullable();
            $table->bigInteger('total_cost_cents')->nullable();
            $table->boolean('requires_inspection')->default(false);
            $table->timestamp('quote_confirmed_at')->nullable();
            $table->foreignId('accepted_by_operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_requests');
    }
};
