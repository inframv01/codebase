<?php

namespace Database\Seeders;

use App\Models\Atoll;
use App\Models\Island;
use Illuminate\Database\Seeder;

class IslandSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'HA' => ['Dhidhdhoo'],
            'HDH' => ['Kulhudhuffushi'],
            'SH' => ['Funadhoo'],
            'NO' => ['Manadhoo'],
            'RA' => ['Ungoofaaru'],
            'BA' => ['Eydhafushi'],
            'LH' => ['Naifaru'],
            'KA' => ['Thulusdhoo', 'Hulhumale', 'Male'],
            'AA' => ['Rasdhoo'],
            'ADH' => ['Mahibadhoo'],
            'VA' => ['Felidhoo'],
            'ME' => ['Muli'],
            'FA' => ['Nilandhoo'],
            'DH' => ['Kudahuvadhoo'],
            'TH' => ['Veymandoo'],
            'L' => ['Fonadhoo'],
            'GA' => ['Villingili'],
            'GDH' => ['Thinadhoo'],
            'GN' => ['Fuvahmulah'],
            'S' => ['Hithadhoo'],
        ])->each(function (array $islands, string $atollCode): void {
            $atoll = Atoll::query()->where('code', $atollCode)->firstOrFail();

            foreach ($islands as $islandName) {
                Island::query()->updateOrCreate([
                    'atoll_id' => $atoll->id,
                    'name' => $islandName,
                ], []);
            }
        });
    }
}
