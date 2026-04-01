<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $sports = DB::table('sports')->pluck('id', 'name');
        $categories = DB::table('exercise_categories')->pluck('id', 'name');

        $rows = [
            [
                'name' => 'Developpe couche halteres',
                'description' => 'Exercice de poussee horizontal pour les pectoraux, epaules et triceps.',
                'exercise_category_id' => $categories['Push'],
                'sport_id' => $sports['Musculation'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Rowing barre',
                'description' => 'Tirage horizontal pour renforcer le dos et l arriere des epaules.',
                'exercise_category_id' => $categories['Pull'],
                'sport_id' => $sports['Musculation'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Squat goblet',
                'description' => 'Squat accessible pour travailler jambes, gainage et posture.',
                'exercise_category_id' => $categories['Legs'],
                'sport_id' => $sports['Musculation'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Pompes strictes',
                'description' => 'Mouvement de base en calisthenie pour le haut du corps.',
                'exercise_category_id' => $categories['Push'],
                'sport_id' => $sports['Calisthenie'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Tractions pronation',
                'description' => 'Exercice de tirage vertical au poids du corps.',
                'exercise_category_id' => $categories['Pull'],
                'sport_id' => $sports['Calisthenie'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Dips',
                'description' => 'Poussee verticale pour triceps, epaules et pectoraux.',
                'exercise_category_id' => $categories['Push'],
                'sport_id' => $sports['Calisthenie'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Planche abdominale',
                'description' => 'Gainage statique pour stabiliser la sangle abdominale.',
                'exercise_category_id' => $categories['Core'],
                'sport_id' => $sports['Calisthenie'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Salutation au soleil',
                'description' => 'Enchainement yoga pour echauffer le corps et fluidifier la respiration.',
                'exercise_category_id' => $categories['Mobilite'],
                'sport_id' => $sports['Yoga'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Posture du guerrier',
                'description' => 'Posture yoga pour renforcer les jambes et ouvrir les hanches.',
                'exercise_category_id' => $categories['Legs'],
                'sport_id' => $sports['Yoga'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Hundred',
                'description' => 'Exercice pilates de gainage dynamique et de respiration controlee.',
                'exercise_category_id' => $categories['Core'],
                'sport_id' => $sports['Pilates'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Pont de hanches',
                'description' => 'Exercice doux pour activer la chaine posterieure et le bassin.',
                'exercise_category_id' => $categories['Legs'],
                'sport_id' => $sports['Pilates'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Ouverture thoracique',
                'description' => 'Mobilisation pour ameliorer l extension du haut du dos.',
                'exercise_category_id' => $categories['Mobilite'],
                'sport_id' => $sports['Mobilite'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Marche active',
                'description' => 'Cardio modere pour augmenter la depense sans impact eleve.',
                'exercise_category_id' => $categories['Cardio'],
                'sport_id' => $sports['Cardio doux'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Respiration diaphragmatique',
                'description' => 'Travail de respiration pour recuperation, stress et posture.',
                'exercise_category_id' => $categories['Respiration'],
                'sport_id' => $sports['Meditation'],
                'user_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('exercises')->updateOrInsert(
                ['name' => $row['name'], 'user_id' => $row['user_id']],
                [
                    'description' => $row['description'],
                    'exercise_category_id' => $row['exercise_category_id'],
                    'sport_id' => $row['sport_id'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );
        }
    }
}
