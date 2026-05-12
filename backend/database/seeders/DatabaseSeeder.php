<?php

namespace Database\Seeders;

use App\Models\Atoll;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AtollSeeder::class,
            IslandSeeder::class,
            TransportProviderSeeder::class,
            PricingRuleSeeder::class,
        ]);

        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'id_card_number' => 'A00000001',
            'atoll_id' => Atoll::query()->value('id'),
            'island_id' => null,
            'house_name' => 'Test House',
            'floor' => '1',
            'password' => 'password',
        ]);
    }
}
