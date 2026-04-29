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
            ['Developpe incline halteres', 'Halteres'],
            ['Developpe incline halteres', 'Banc'],
            ['Developpe militaire', 'Barre olympique'],
            ['Elevations laterales', 'Halteres'],
            ['Rowing barre', 'Barre olympique'],
            ['Tirage vertical', 'Machine guidee'],
            ['Rowing poulie basse', 'Machine guidee'],
            ['Face pull', 'Machine guidee'],
            ['Squat goblet', 'Kettlebell'],
            ['Squat goblet', 'Halteres'],
            ['Presse a cuisses', 'Machine guidee'],
            ['Souleve de terre roumain', 'Barre olympique'],
            ['Fentes marchees', 'Halteres'],
            ['Hip thrust', 'Barre olympique'],
            ['Leg curl', 'Machine guidee'],
            ['Curl biceps halteres', 'Halteres'],
            ['Extension triceps poulie', 'Machine guidee'],
            ['Crunch cable', 'Machine guidee'],
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

        DB::table('exercise_equipment')->insertOrIgnore($rows);
    }
}
