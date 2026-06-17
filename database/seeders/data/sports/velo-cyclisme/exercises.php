<?php

return [
    'name' => 'Vélo / cyclisme',
    'display_name' => 'Vélo',
    'sort_order' => 3,
    'activities' => [
        [
            'name' => 'Sortie endurance',
            'description' => 'Sortie à intensité modérée et régulière.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'speed_kmh', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Sortie longue',
            'description' => 'Sortie de longue durée à allure maîtrisée.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'speed_kmh', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Sortie récupération',
            'description' => 'Sortie légère favorisant la récupération.',
            'category' => 'recuperation',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Montée régulière',
            'description' => 'Effort continu en côte.',
            'category' => 'force',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Montée explosive',
            'description' => 'Effort intense en côte sur courte durée.',
            'category' => 'puissance',
            'metrics' => ['sets', 'distance_meters', 'elevation_gain_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Sprint',
            'description' => 'Accélération maximale sur courte distance.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'speed_kmh', 'perceived_effort'],
        ],
        [
            'name' => 'Sprint lancé',
            'description' => 'Sprint réalisé après une phase d\'élan.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'speed_kmh'],
        ],
        [
            'name' => 'Intervalle court',
            'description' => 'Alternance d\'efforts courts et récupérations.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'duration_minutes', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Intervalle long',
            'description' => 'Alternance d\'efforts prolongés et récupérations.',
            'category' => 'endurance',
            'metrics' => ['sets', 'duration_minutes', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Effort au seuil',
            'description' => 'Maintien d\'une intensité soutenue et stable.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Contre-la-montre',
            'description' => 'Effort individuel continu sur une distance donnée.',
            'category' => 'endurance',
            'metrics' => ['distance_meters', 'duration_minutes', 'speed_kmh'],
        ],
        [
            'name' => 'Relance',
            'description' => 'Accélération après un ralentissement.',
            'category' => 'puissance',
            'metrics' => ['sets', 'duration_minutes', 'perceived_effort'],
        ],
        [
            'name' => 'Cadence élevée',
            'description' => 'Pédalage à fréquence élevée et contrôlée.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Pédalage une jambe',
            'description' => 'Travail de fluidité du mouvement de pédalage.',
            'category' => 'technique',
            'metrics' => ['sets', 'duration_minutes'],
        ],
        [
            'name' => 'Vélocité',
            'description' => 'Travail de rapidité de pédalage.',
            'category' => 'technique',
            'metrics' => ['sets', 'duration_minutes'],
        ],
        [
            'name' => 'Gravel endurance',
            'description' => 'Sortie longue sur chemins et pistes roulantes.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Parcours vallonné',
            'description' => 'Sortie alternant montées et portions roulantes.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Sortie progressive',
            'description' => 'Intensité augmentée progressivement durant la sortie.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'speed_kmh', 'heart_rate_bpm'],
        ],
    ],
];
