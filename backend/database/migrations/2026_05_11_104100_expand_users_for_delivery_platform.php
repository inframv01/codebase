<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('id_card_number')->nullable()->unique()->after('name');
            $table->foreignId('atoll_id')->nullable()->after('id_card_number');
            $table->foreignId('island_id')->nullable()->after('atoll_id');
            $table->string('house_name')->nullable()->after('island_id');
            $table->string('floor')->nullable()->after('house_name');
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('role')->default('customer')->index()->after('password');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('password')->nullable(false)->change();
            $table->dropColumn([
                'id_card_number',
                'atoll_id',
                'island_id',
                'house_name',
                'floor',
                'google_id',
                'role',
            ]);
        });
    }
};
