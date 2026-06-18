<?php

namespace Database\Seeders\Demo\CommunityProfiles;

class ZoeProfileSeeder extends CommunityProfileSeeder
{
    protected function profile(): array
    {
        return [
            'email' => 'zoe@evolyx.local',
            'pseudo' => 'zoe_lift',
            'first_name' => 'Zoe',
            'sex' => 'female',
            'height' => 170,
            'activity_level' => 'moderate',
            'birth_date' => '1997-11-18',
            'sports' => ['Fitness / musculation', 'Yoga', 'Marche / randonnée'],
        ];
    }

    protected function sharedSessions(): array
    {
        return [
            [
                'session' => 'Haut du corps partage',
                'description' => 'Renforcement haut du corps.',
                'day' => 9,
                'hour' => 19,
                'post_title' => 'Retour propre sur le haut du corps',
                'post_content' => 'Tractions plus fluides que la semaine derniere.',
                'performances' => [
                    ['exercise' => 'Tractions', 'weight' => 0.00, 'repetitions' => 9],
                    ['exercise' => 'Développé militaire', 'weight' => 29.00, 'repetitions' => 7],
                    ['exercise' => 'Gainage planche', 'duration_minutes' => 3],
                ],
            ],
            [
                'session' => 'Yoga récupération partage',
                'description' => 'Respiration, mobilité et relâchement.',
                'day' => 15,
                'hour' => 8,
                'duration_minutes' => 38,
                'post_title' => 'Mobilite du matin',
                'post_content' => 'Session douce avant la journée.',
                'performances' => [
                    ['exercise' => 'Respiration contrôlée', 'duration_minutes' => 8],
                    ['exercise' => 'Posture du pigeon', 'duration_minutes' => 10],
                    ['exercise' => 'Relaxation finale', 'duration_minutes' => 8],
                ],
            ],
        ];
    }
}
