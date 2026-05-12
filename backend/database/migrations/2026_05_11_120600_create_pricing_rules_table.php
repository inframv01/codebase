<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('scope_type')->index();
            $table->unsignedBigInteger('scope_id')->index();
            $table->string('service_type')->index();
            $table->bigInteger('fixed_cost_cents');
            $table->bigInteger('variable_rate_cents_per_kg')->default(0);
            $table->bigInteger('min_charge_cents');
            $table->boolean('requires_inspection')->default(false);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
