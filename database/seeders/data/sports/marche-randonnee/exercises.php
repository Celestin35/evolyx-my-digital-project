<?php

return [
    'name' => 'Marche / randonnée',
    'display_name' => 'Marche',
    'sort_order' => 5,
    'activities' => [
        [
            'name' => 'Marche loisir',
            'description' => 'Marche à allure confortable sur terrain varié.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Marche sportive',
            'description' => 'Marche soutenue à rythme régulier.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'speed_kmh', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Randonnée courte',
            'description' => 'Parcours de durée modérée en milieu naturel.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters'],
        ],
        [
            'name' => 'Randonnée journée',
            'description' => 'Sortie prolongée sur une journée.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Grande randonnée',
            'description' => 'Parcours de longue distance avec gestion de l\'effort.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Marche en montée',
            'description' => 'Progression continue sur pente ascendante.',
            'category' => 'force',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Montée raide',
            'description' => 'Effort soutenu sur forte pente.',
            'category' => 'force',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Marche rapide en montée',
            'description' => 'Montée dynamique à rythme soutenu.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Descente technique',
            'description' => 'Travail du placement et du contrôle en descente.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'distance_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Parcours vallonné',
            'description' => 'Alternance de montées et descentes.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters'],
        ],
        [
            'name' => 'Traversée en montagne',
            'description' => 'Progression sur itinéraire de montagne.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters'],
        ],
        [
            'name' => 'Marche nordique',
            'description' => 'Marche active avec engagement du haut du corps.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'speed_kmh', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Marche récupération',
            'description' => 'Marche légère favorisant la récupération.',
            'category' => 'recuperation',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Randonnée progressive',
            'description' => 'Sortie avec augmentation graduelle de l\'intensité.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Marche d\'orientation',
            'description' => 'Progression avec navigation sur un itinéraire défini.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'distance_meters'],
        ],
        [
            'name' => 'Parcours technique',
            'description' => 'Déplacement sur terrain irrégulier ou rocheux.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'distance_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Randonnée montagne',
            'description' => 'Sortie avec dénivelé important en montagne.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Reconnaissance d\'itinéraire',
            'description' => 'Découverte ou préparation d\'un parcours.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters'],
        ],
    ],
];
