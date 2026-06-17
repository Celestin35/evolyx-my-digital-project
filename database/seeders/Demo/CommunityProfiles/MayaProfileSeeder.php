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
            'sports' => ['Fitness / musculation', 'Pilates'],
        ];
    }

    protected function sharedSessions(): array
    {
        return [
            [
                'session' => 'Full body controle',
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
                'session' => 'Pilates renforcement',
                'description' => 'Controle et gainage avec accessoires.',
                'day' => 11,
                'hour' => 12,
                'minute' => 30,
                'post_title' => 'Core plus stable',
                'post_content' => 'Seance courte mais propre.',
                'performances' => [
                    ['exercise' => 'Hundred', 'repetitions' => 40],
                    ['exercise' => 'Pont de hanches', 'repetitions' => 15],
                    ['exercise' => 'Pilates avec élastique', 'duration_minutes' => 18],
                ],
            ],
        ];
    }
}
