<?php

return [
    [
        'sport' => 'Fitness / musculation',
        'sessions' => [
            [
                'name' => 'Full body musculation',
                'description' => 'Séance complète pour suivre les principaux mouvements de force.',
                'exercises' => ['Squat', 'Développé couché', 'Tractions', 'Gainage'],
            ],
            [
                'name' => 'Push musculation',
                'description' => 'Poussée haut du corps avec pectoraux, épaules et triceps.',
                'exercises' => ['Développé couché', 'Développé militaire', 'Presse à cuisses'],
            ],
            [
                'name' => 'Jambes musculation',
                'description' => 'Travail des jambes et de la chaîne postérieure.',
                'exercises' => ['Squat', 'Presse à cuisses', 'Soulevé de terre', 'Gainage'],
            ],
        ],
    ],
    [
        'sport' => 'Course à pied / running',
        'sessions' => [
            [
                'name' => 'Running endurance',
                'description' => 'Sortie de base pour construire l’endurance aérobie.',
                'exercises' => ['Footing endurance', 'Récupération active running'],
            ],
            [
                'name' => 'Running vitesse',
                'description' => 'Séance orientée allure, fractionné et vitesse.',
                'exercises' => ['Fractionné court', 'Fractionné long', 'Tempo run'],
            ],
            [
                'name' => 'Trail et côtes',
                'description' => 'Travail du dénivelé, de la puissance et des sentiers.',
                'exercises' => ['Côtes running', 'Trail running'],
            ],
        ],
    ],
    [
        'sport' => 'Vélo / cyclisme',
        'sessions' => [
            [
                'name' => 'Vélo endurance',
                'description' => 'Sortie cyclisme régulière pour suivre durée, distance et vitesse.',
                'exercises' => ['Sortie endurance vélo', 'Sortie longue vélo'],
            ],
            [
                'name' => 'Vélo intensité',
                'description' => 'Séance de puissance et de vitesse à vélo.',
                'exercises' => ['Fractionné vélo', 'Sprint vélo', 'Montée de côte vélo'],
            ],
            [
                'name' => 'Vélo récupération',
                'description' => 'Séance légère en extérieur ou en intérieur.',
                'exercises' => ['Vélo récupération', 'Home trainer'],
            ],
        ],
    ],
    [
        'sport' => 'Natation',
        'sessions' => [
            [
                'name' => 'Natation endurance',
                'description' => 'Séance continue pour suivre distance et durée en bassin.',
                'exercises' => ['Nage libre endurance', 'Brasse endurance'],
            ],
            [
                'name' => 'Natation technique',
                'description' => 'Travail technique avec éducatifs et matériel.',
                'exercises' => ['Crawl technique', 'Battements avec planche', 'Pull buoy'],
            ],
            [
                'name' => 'Natation vitesse',
                'description' => 'Séance courte pour travailler la vitesse et l’intensité.',
                'exercises' => ['Sprint natation', 'Récupération natation'],
            ],
        ],
    ],
    [
        'sport' => 'Marche / randonnée',
        'sessions' => [
            [
                'name' => 'Marche active',
                'description' => 'Séance simple pour suivre marche rapide, durée et distance.',
                'exercises' => ['Marche rapide', 'Marche en côte'],
            ],
            [
                'name' => 'Randonnée',
                'description' => 'Sortie randonnée avec distance et dénivelé.',
                'exercises' => ['Randonnée courte', 'Randonnée longue', 'Randonnée avec dénivelé'],
            ],
            [
                'name' => 'Marche récupération',
                'description' => 'Sortie légère pour bouger sans intensité élevée.',
                'exercises' => ['Marche détente', 'Marche nordique'],
            ],
        ],
    ],
    [
        'sport' => 'Yoga',
        'sessions' => [
            [
                'name' => 'Yoga mobilité',
                'description' => 'Séance douce pour mobilité, respiration et récupération.',
                'exercises' => ['Salutation au soleil', 'Mobilité yoga', 'Respiration pranayama'],
            ],
            [
                'name' => 'Yoga dynamique',
                'description' => 'Séance plus active autour des enchaînements et équilibres.',
                'exercises' => ['Vinyasa yoga', 'Posture du guerrier', 'Équilibres yoga'],
            ],
        ],
    ],
    [
        'sport' => 'Pilates',
        'sessions' => [
            [
                'name' => 'Pilates matwork',
                'description' => 'Séance au sol orientée contrôle, gainage et mobilité.',
                'exercises' => ['Pilates matwork', 'Hundred', 'Roll up'],
            ],
            [
                'name' => 'Pilates renforcement',
                'description' => 'Séance Pilates avec accessoires et travail des hanches.',
                'exercises' => ['Pont de hanches', 'Side kick', 'Pilates avec élastique'],
            ],
        ],
    ],
];
