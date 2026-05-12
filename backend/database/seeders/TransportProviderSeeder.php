<?php

namespace Database\Seeders;

use App\Models\Boat;
use App\Models\Island;
use App\Models\IslandGroup;
use App\Models\TransportProvider;
use Illuminate\Database\Seeder;

class TransportProviderSeeder extends Seeder
{
    public function run(): void
    {
        $northGroup = IslandGroup::query()->updateOrCreate(['name' => 'Northern Islands']);
        $centralGroup = IslandGroup::query()->updateOrCreate(['name' => 'Central Islands']);
        $southGroup = IslandGroup::query()->updateOrCreate(['name' => 'Southern Islands']);

        $northGroup->islands()->sync(Island::query()->whereIn('name', ['Dhidhdhoo', 'Kulhudhuffushi', 'Funadhoo', 'Manadhoo'])->pluck('id'));
        $centralGroup->islands()->sync(Island::query()->whereIn('name', ['Male', 'Hulhumale', 'Thulusdhoo', 'Rasdhoo', 'Mahibadhoo', 'Kudahuvadhoo'])->pluck('id'));
        $southGroup->islands()->sync(Island::query()->whereIn('name', ['Fonadhoo', 'Villingili', 'Thinadhoo', 'Fuvahmulah', 'Hithadhoo'])->pluck('id'));

        $providers = [
            ['name' => 'Northern Cargo Line', 'type' => 'boat', 'group' => $northGroup],
            ['name' => 'Central Atoll Ferries', 'type' => 'boat', 'group' => $centralGroup],
            ['name' => 'Southern Logistics', 'type' => 'boat', 'group' => $southGroup],
        ];

        foreach ($providers as $data) {
            $provider = TransportProvider::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'type' => $data['type'],
                    'contact_name' => 'Operations Desk',
                    'contact_phone' => '+9607000000',
                    'active' => true,
                ]
            );

            $boat = Boat::query()->updateOrCreate(
                ['transport_provider_id' => $provider->id, 'name' => $provider->name.' 01'],
                ['capacity_kg' => 500, 'active' => true]
            );

            $boat->islandGroups()->syncWithoutDetaching([$data['group']->id]);
        }
    }
}
