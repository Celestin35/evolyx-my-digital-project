<?php

return [
    'name' => 'Trail',
    'display_name' => 'Trail',
    'sort_order' => 4,
    'activities' => [
        [
            'name' => 'Trail endurance',
            'description' => 'Sortie à allure confortable sur sentiers.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Sortie longue trail',
            'description' => 'Sortie prolongée sur terrain naturel varié.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Trail récupération',
            'description' => 'Sortie légère favorisant la récupération.',
            'category' => 'recuperation',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Montée continue',
            'description' => 'Ascension régulière sur une pente prolongée.',
            'category' => 'force',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Répétitions en côte',
            'description' => 'Enchaînement de montées avec récupération.',
            'category' => 'puissance',
            'metrics' => ['sets', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Descente technique',
            'description' => 'Travail du contrôle et du placement en descente.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'distance_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Fractionné en montée',
            'description' => 'Intervalles réalisés sur une pente.',
            'category' => 'puissance',
            'metrics' => ['sets', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Fractionné trail',
            'description' => 'Alternance d\'efforts rapides sur sentiers.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Trail au seuil',
            'description' => 'Effort soutenu sur terrain naturel.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Marche rapide en montée',
            'description' => 'Progression dynamique sur forte pente.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Power hiking',
            'description' => 'Marche active utilisée sur les portions raides.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Parcours technique',
            'description' => 'Progression sur terrain accidenté.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'distance_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Foulées bondissantes en côte',
            'description' => 'Travail de propulsion sur pente modérée.',
            'category' => 'puissance',
            'metrics' => ['sets', 'distance_meters', 'elevation_gain_meters'],
        ],
        [
            'name' => 'Montées de genoux',
            'description' => 'Exercice de coordination pour la foulée.',
            'category' => 'coordination',
            'metrics' => ['sets', 'duration_minutes'],
        ],
        [
            'name' => 'Talons-fesses',
            'description' => 'Exercice de coordination et de fréquence.',
            'category' => 'coordination',
            'metrics' => ['sets', 'duration_minutes'],
        ],
        [
            'name' => 'Accélérations en sentier',
            'description' => 'Accélérations progressives sur terrain naturel.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Trail progression',
            'description' => 'Sortie avec augmentation progressive de l\'intensité.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Enchaînement montée descente',
            'description' => 'Alternance continue de montées et descentes.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Sortie montagne',
            'description' => 'Sortie longue avec dénivelé important.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Reconnaissance de parcours',
            'description' => 'Découverte ou préparation d\'un itinéraire.',
            'category' => 'technique',
            'metrics' => ['duration_minutes', 'distance_meters', 'elevation_gain_meters'],
        ],
    ],
];
