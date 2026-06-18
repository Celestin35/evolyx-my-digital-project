<?php

namespace Database\Seeders\Demo\CommunityProfiles;

class NoahProfileSeeder extends CommunityProfileSeeder
{
    protected function profile(): array
    {
        return [
            'email' => 'noah@evolyx.local',
            'pseudo' => 'noah_run',
            'first_name' => 'Noah',
            'sex' => 'male',
            'height' => 181,
            'activity_level' => 'active',
            'birth_date' => '1994-09-04',
            'sports' => ['Course à pied / running', 'Vélo / cyclisme', 'Trail'],
        ];
    }

    protected function sharedSessions(): array
    {
        return [
            [
                'session' => 'Running endurance partage',
                'description' => 'Sortie calme pour garder le volume.',
                'day' => 7,
                'hour' => 7,
                'duration_minutes' => 48,
                'post_title' => 'Footing propre',
                'post_content' => 'Allure régulière, bonnes sensations cardio.',
                'performances' => [
                    ['exercise' => 'Course en endurance fondamentale', 'duration_minutes' => 42, 'distance_meters' => 7200],
                    ['exercise' => 'Course de récupération', 'duration_minutes' => 6, 'distance_meters' => 800],
                ],
            ],
            [
                'session' => 'Velo endurance partage',
                'description' => 'Sortie vélo régulière avec quelques relances.',
                'day' => 13,
                'hour' => 18,
                'duration_minutes' => 62,
                'post_title' => 'Bonne sortie vélo',
                'post_content' => null,
                'performances' => [
                    ['exercise' => 'Sortie endurance', 'duration_minutes' => 45, 'distance_meters' => 21000],
                    ['exercise' => 'Relance', 'duration_minutes' => 8],
                ],
            ],
            [
                'session' => 'Trail cotes partage',
                'description' => 'Petite séance de dénivelé sur sentiers.',
                'day' => 19,
                'hour' => 8,
                'duration_minutes' => 55,
                'post_title' => 'Cotes en trail',
                'post_content' => 'Bon travail de puissance en montee.',
                'performances' => [
                    ['exercise' => 'Répétitions en côte', 'distance_meters' => 1200],
                    ['exercise' => 'Foulées bondissantes en côte', 'distance_meters' => 300],
                ],
            ],
        ];
    }
}
