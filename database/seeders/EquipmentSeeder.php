<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $catalog = require database_path('seeders/data/sports_catalog.php');

        $equipment = collect($catalog)
            ->flatMap(fn (array $sport) => collect($sport['activities'])->flatMap(fn (array $activity) => $activity['equipment']))
            ->unique()
            ->sort()
            ->values();

        DB::table('equipment')
            ->whereNotIn('name', $equipment)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('exercise_equipment')
                    ->whereColumn('exercise_equipment.equipment_id', 'equipment.id');
            })
            ->delete();

        foreach ($equipment as $item) {
            DB::table('equipment')->updateOrInsert(
                ['name' => $item],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }
    }
}
