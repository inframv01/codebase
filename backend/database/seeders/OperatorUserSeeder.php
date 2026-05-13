<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Atoll;
use App\Models\User;
use Illuminate\Database\Seeder;

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
    }
}
