<?php

namespace Database\Seeders;

use App\Models\Atoll;
use Illuminate\Database\Seeder;

class AtollSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['code' => 'HA', 'name' => 'Haa Alif'],
            ['code' => 'HDH', 'name' => 'Haa Dhaalu'],
            ['code' => 'SH', 'name' => 'Shaviyani'],
            ['code' => 'NO', 'name' => 'Noonu'],
            ['code' => 'RA', 'name' => 'Raa'],
            ['code' => 'BA', 'name' => 'Baa'],
            ['code' => 'LH', 'name' => 'Lhaviyani'],
            ['code' => 'KA', 'name' => 'Kaafu'],
            ['code' => 'AA', 'name' => 'Alif Alif'],
            ['code' => 'ADH', 'name' => 'Alif Dhaalu'],
            ['code' => 'VA', 'name' => 'Vaavu'],
            ['code' => 'ME', 'name' => 'Meemu'],
            ['code' => 'FA', 'name' => 'Faafu'],
            ['code' => 'DH', 'name' => 'Dhaalu'],
            ['code' => 'TH', 'name' => 'Thaa'],
            ['code' => 'L', 'name' => 'Laamu'],
            ['code' => 'GA', 'name' => 'Gaafu Alif'],
            ['code' => 'GDH', 'name' => 'Gaafu Dhaalu'],
            ['code' => 'GN', 'name' => 'Gnaviyani'],
            ['code' => 'S', 'name' => 'Seenu'],
        ])->each(fn (array $atoll) => Atoll::query()->updateOrCreate(['code' => $atoll['code']], $atoll));
    }
}
