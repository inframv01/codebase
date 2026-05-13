<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Atoll;
use App\Models\Island;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate([
            'email' => 'operator@example.com',
        ], [
            'name' => 'Default Operator',
            'id_card_number' => 'O00000001',
            'atoll_id' => Atoll::query()->value('id'),
            'island_id' => null,
            'house_name' => 'Operator House',
            'floor' => '1',
            'password' => 'password',
            'role' => UserRole::Operator,
        ]);

        // Create operator with verified email for atoll K
        $atoll = Atoll::query()->where('code', 'K')->firstOrFail();
        $island = Island::query()->where('atoll_id', $atoll->id)->first();

        User::query()->firstOrCreate([
            'email' => 'operator.kaafu@example.com',
        ], [
            'name' => 'Kaafu Operator',
            'id_card_number' => 'O00000002',
            'atoll_id' => $atoll->id,
            'island_id' => $island?->id,
            'house_name' => 'Operator Center',
            'floor' => '1',
            'email_verified_at' => now(),
            'password' => Hash::make('password12!a'),
            'role' => UserRole::Operator,
        ]);
    }
}
