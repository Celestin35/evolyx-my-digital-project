<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExerciseEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $exerciseIds = DB::table('exercises')->pluck('id', 'name');
        $equipmentIds = DB::table('equipment')->pluck('id', 'name');

        $pairs = [
            ['Developpe couche halteres', 'Halteres'],
            ['Developpe couche halteres', 'Banc'],
            ['Rowing barre', 'Barre olympique'],
            ['Squat goblet', 'Kettlebell'],
            ['Squat goblet', 'Halteres'],
            ['Pompes strictes', 'Poids du corps'],
            ['Tractions pronation', 'Barre de traction'],
            ['Dips', 'Poids du corps'],
            ['Planche abdominale', 'Poids du corps'],
            ['Salutation au soleil', 'Tapis'],
            ['Posture du guerrier', 'Tapis'],
            ['Hundred', 'Tapis'],
            ['Pont de hanches', 'Tapis'],
            ['Ouverture thoracique', 'Foam roller'],
            ['Marche active', 'Poids du corps'],
            ['Respiration diaphragmatique', 'Tapis'],
        ];

        $rows = [];
        foreach ($pairs as [$exerciseName, $equipmentName]) {
            $rows[] = [
                'exercise_id' => $exerciseIds[$exerciseName],
                'equipment_id' => $equipmentIds[$equipmentName],
            ];
        }

        DB::table('exercise_equipment')->upsert($rows, ['exercise_id', 'equipment_id'], []);
    }
}
