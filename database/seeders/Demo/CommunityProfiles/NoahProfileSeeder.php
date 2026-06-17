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
            'sports' => ['Course à pied / running', 'Vélo / cyclisme'],
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
                'post_content' => 'Allure reguliere, bonnes sensations cardio.',
                'performances' => [
                    ['exercise' => 'Footing endurance', 'duration_minutes' => 42, 'distance_meters' => 7200],
                    ['exercise' => 'Récupération active running', 'duration_minutes' => 6, 'distance_meters' => 800],
                ],
            ],
            [
                'session' => 'Velo intensite partage',
                'description' => 'Intervalles courts et retour au calme.',
                'day' => 13,
                'hour' => 18,
                'duration_minutes' => 62,
                'post_title' => 'Bonne intensite velo',
                'post_content' => null,
                'performances' => [
                    ['exercise' => 'Fractionné vélo', 'duration_minutes' => 28, 'distance_meters' => 13500],
                    ['exercise' => 'Vélo récupération', 'duration_minutes' => 20, 'distance_meters' => 6200],
                ],
            ],
        ];
    }
}
