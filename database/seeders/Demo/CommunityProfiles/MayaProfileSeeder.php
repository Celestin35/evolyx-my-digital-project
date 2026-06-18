<?php

namespace Database\Seeders\Demo\CommunityProfiles;

class MayaProfileSeeder extends CommunityProfileSeeder
{
    protected function profile(): array
    {
        return [
            'email' => 'maya@evolyx.local',
            'pseudo' => 'maya_fit',
            'first_name' => 'Maya',
            'sex' => 'female',
            'height' => 164,
            'activity_level' => 'active',
            'birth_date' => '1998-03-12',
            'sports' => ['Fitness / musculation', 'Pilates', 'Natation'],
        ];
    }

    protected function sharedSessions(): array
    {
        return [
            [
                'session' => 'Full body contrôle',
                'description' => 'Travail propre sur les mouvements de base.',
                'day' => 5,
                'hour' => 18,
                'post_title' => 'Full body solide',
                'post_content' => 'Bonnes sensations, surtout sur le squat.',
                'performances' => [
                    ['exercise' => 'Squat', 'weight' => 68.50, 'repetitions' => 8],
                    ['exercise' => 'Tractions', 'weight' => 0.00, 'repetitions' => 7],
                    ['exercise' => 'Curl biceps', 'weight' => 12.00, 'repetitions' => 10],
                ],
            ],
            [
                'session' => 'Pilates centre partage',
                'description' => 'Controle du centre et stabilité.',
                'day' => 11,
                'hour' => 12,
                'minute' => 30,
                'post_title' => 'Core plus stable',
                'post_content' => 'Séance courte mais propre.',
                'performances' => [
                    ['exercise' => 'The Hundred', 'duration_minutes' => 3],
                    ['exercise' => 'Plank Pilates', 'duration_minutes' => 2],
                    ['exercise' => 'Teaser', 'duration_minutes' => 2],
                ],
            ],
            [
                'session' => 'Natation technique partage',
                'description' => 'Travail technique tranquille en bassin.',
                'day' => 17,
                'hour' => 7,
                'duration_minutes' => 45,
                'post_title' => 'Technique en bassin',
                'post_content' => 'Crawl plus fluide sur la fin.',
                'performances' => [
                    ['exercise' => 'Éducatif crawl', 'distance_meters' => 600],
                    ['exercise' => 'Battements de jambes', 'distance_meters' => 400],
                    ['exercise' => 'Crawl', 'duration_minutes' => 18, 'distance_meters' => 900],
                ],
            ],
        ];
    }
}
