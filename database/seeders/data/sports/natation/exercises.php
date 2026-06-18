<?php

return [
    'name' => 'Natation',
    'display_name' => 'Natation',
    'sort_order' => 6,
    'activities' => [
        [
            'name' => 'Nage libre',
            'description' => 'Nage continue à allure libre.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Crawl',
            'description' => 'Nage en crawl sur distance définie.',
            'category' => 'endurance',
            'metrics' => ['distance_meters', 'duration_minutes', 'perceived_effort'],
        ],
        [
            'name' => 'Brasse',
            'description' => 'Nage en brasse à allure contrôlée.',
            'category' => 'endurance',
            'metrics' => ['distance_meters', 'duration_minutes', 'perceived_effort'],
        ],
        [
            'name' => 'Dos crawlé',
            'description' => 'Nage sur le dos en mouvement continu.',
            'category' => 'endurance',
            'metrics' => ['distance_meters', 'duration_minutes'],
        ],
        [
            'name' => 'Papillon',
            'description' => 'Nage simultanée avec ondulation du corps.',
            'category' => 'puissance',
            'metrics' => ['distance_meters', 'duration_minutes', 'perceived_effort'],
        ],
        [
            'name' => 'Sprint crawl',
            'description' => 'Effort maximal sur courte distance.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Intervalles natation',
            'description' => 'Alternance d\'efforts et de récupération.',
            'category' => 'vitesse',
            'metrics' => ['sets', 'distance_meters', 'perceived_effort'],
        ],
        [
            'name' => 'Nage au seuil',
            'description' => 'Maintien d\'une intensité soutenue.',
            'category' => 'endurance',
            'metrics' => ['distance_meters', 'duration_minutes', 'heart_rate_bpm', 'perceived_effort'],
        ],
        [
            'name' => 'Nage continue',
            'description' => 'Effort prolongé sans interruption.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'heart_rate_bpm'],
        ],
        [
            'name' => 'Éducatif crawl',
            'description' => 'Travail technique spécifique au crawl.',
            'category' => 'technique',
            'metrics' => ['sets', 'distance_meters'],
        ],
        [
            'name' => 'Éducatif brasse',
            'description' => 'Travail technique spécifique à la brasse.',
            'category' => 'technique',
            'metrics' => ['sets', 'distance_meters'],
        ],
        [
            'name' => 'Éducatif dos crawlé',
            'description' => 'Travail technique spécifique au dos crawlé.',
            'category' => 'technique',
            'metrics' => ['sets', 'distance_meters'],
        ],
        [
            'name' => 'Battements de jambes',
            'description' => 'Travail spécifique des battements.',
            'category' => 'technique',
            'metrics' => ['sets', 'distance_meters'],
        ],
        [
            'name' => 'Ondulations',
            'description' => 'Travail du mouvement ondulatoire.',
            'category' => 'technique',
            'metrics' => ['sets', 'distance_meters'],
        ],
        [
            'name' => 'Nage quatre nages',
            'description' => 'Enchaînement des quatre styles de nage.',
            'category' => 'coordination',
            'metrics' => ['distance_meters', 'duration_minutes', 'perceived_effort'],
        ],
        [
            'name' => 'Départs et virages',
            'description' => 'Travail des phases techniques de course.',
            'category' => 'technique',
            'metrics' => ['sets', 'successful_attempts', 'total_attempts'],
        ],
        [
            'name' => 'Nage récupération',
            'description' => 'Nage légère favorisant la récupération.',
            'category' => 'recuperation',
            'metrics' => ['duration_minutes', 'distance_meters'],
        ],
        [
            'name' => 'Endurance progressive',
            'description' => 'Augmentation progressive de l\'intensité.',
            'category' => 'endurance',
            'metrics' => ['duration_minutes', 'distance_meters', 'perceived_effort'],
        ],
    ],
];
