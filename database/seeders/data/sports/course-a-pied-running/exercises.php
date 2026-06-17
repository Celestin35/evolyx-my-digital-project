<?php

return [
    'name' => 'Course à pied / running',
    'display_name' => 'Course à pied',
    'sort_order' => 1,
    'activities' => [
        [
            'name' => 'Course en endurance fondamentale',
            'description' => 'Course à allure confortable et régulière.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'pace_min_per_km', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Course tempo',
            'description' => 'Course soutenue sur une durée continue.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'pace_min_per_km', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Sortie longue',
            'description' => 'Course de durée prolongée à allure maîtrisée.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'pace_min_per_km', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Fractionné court',
            'description' => 'Alternance d\'efforts courts et de récupération.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'pace_min_per_km', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Fractionné long',
            'description' => 'Alternance d\'efforts prolongés et de récupération.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'pace_min_per_km', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Intervalles pyramide',
            'description' => 'Intervalles de durée ou distance croissante puis décroissante.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'pace_min_per_km'],
        ],
        [
            'name' => 'Sprints',
            'description' => 'Accélérations maximales sur courte distance.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'speed_kmh', 'perceived_effort'],
        ],
        [
            'name' => 'Accélérations progressives',
            'description' => 'Montée progressive de l\'allure sur une courte distance.',
            'category' => 'technique',
            'metrics' => ['sets', 'distance_meters', 'speed_kmh'],
        ],
        [
            'name' => 'Foulées bondissantes',
            'description' => 'Travail de propulsion et d\'amplitude de foulée.',
            'category' => 'puissance',
            'metrics' => ['sets', 'distance_meters'],
        ],
        [
            'name' => 'Montées de genoux',
            'description' => 'Exercice technique de fréquence et coordination.',
            'category' => 'technique',
            'metrics' => ['sets', 'duration_minutes'],
        ],
        [
            'name' => 'Talons-fesses',
            'description' => 'Exercice technique de coordination de course.',
            'category' => 'technique',
            'metrics' => ['sets', 'duration_minutes'],
        ],
        [
            'name' => 'Pas chassés',
            'description' => 'Travail de coordination latérale.',
            'category' => 'coordination',
            'metrics' => ['sets', 'distance_meters'],
        ],
        [
            'name' => 'Course de récupération',
            'description' => 'Course légère favorisant la récupération.',
            'category' => 'recuperation',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Marche active',
            'description' => 'Déplacement dynamique à allure soutenue.',
            'category' => 'recuperation',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Course en progression',
            'description' => 'Allure augmentée progressivement durant la sortie.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'pace_min_per_km', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Course au seuil',
            'description' => 'Effort soutenu proche du seuil d\'endurance.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'pace_min_per_km', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Intervalles en côte',
            'description' => 'Répétitions d\'efforts en montée.',
            'category' => 'puissance',
            'metrics' => ['sets', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Course sur piste',
            'description' => 'Travail d\'allure sur une piste d\'athlétisme.',
            'category' => 'vitesse',
            'metrics' => ['duration_minutes', 'distance_meters', 'pace_min_per_km'],
        ],
    ],
];
