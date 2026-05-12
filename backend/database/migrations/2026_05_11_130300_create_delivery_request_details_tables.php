<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_post_office_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('tracking_number');
            $table->string('screenshot_path');
            $table->timestamps();
        });

        Schema::create('delivery_male_address_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->jsonb('address');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->timestamps();
        });

        Schema::create('delivery_shop_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->jsonb('shop_address');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('quote_path')->nullable();
            $table->jsonb('items')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE delivery_shop_details ADD CONSTRAINT delivery_shop_details_quote_or_items_check CHECK ((quote_path IS NOT NULL AND items IS NULL) OR (quote_path IS NULL AND items IS NOT NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_shop_details');
        Schema::dropIfExists('delivery_male_address_details');
        Schema::dropIfExists('delivery_post_office_details');
    }
};
