<?php

return [
    'name' => 'Fitness / musculation',
    'display_name' => 'Musculation',
    'sort_order' => 2,
    'activities' => [
        [
            'name' => 'Développé couché',
            'description' => 'Poussée horizontale pour le haut du corps.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg', 'perceived_effort'],
        ],
        [
            'name' => 'Développé incliné',
            'description' => 'Poussée inclinée pour le haut du corps.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg', 'perceived_effort'],
        ],
        [
            'name' => 'Développé militaire',
            'description' => 'Poussée verticale au-dessus de la tête.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg', 'perceived_effort'],
        ],
        [
            'name' => 'Élévations latérales',
            'description' => 'Élévation contrôlée des bras sur les côtés.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg'],
        ],
        [
            'name' => 'Pompes',
            'description' => 'Poussée au poids du corps.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'perceived_effort'],
        ],
        [
            'name' => 'Tractions',
            'description' => 'Tirage vertical au poids du corps.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg', 'perceived_effort'],
        ],
        [
            'name' => 'Rowing barre',
            'description' => 'Tirage horizontal avec charge.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg', 'perceived_effort'],
        ],
        [
            'name' => 'Tirage vertical',
            'description' => 'Tirage vertical guidé.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg'],
        ],
        [
            'name' => 'Curl biceps',
            'description' => 'Flexion des bras avec charge.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg'],
        ],
        [
            'name' => 'Extension triceps',
            'description' => 'Extension des bras avec charge.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg'],
        ],
        [
            'name' => 'Squat',
            'description' => 'Flexion complète des jambes avec charge.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg', 'perceived_effort'],
        ],
        [
            'name' => 'Presse à cuisses',
            'description' => 'Poussée des jambes sur machine.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg'],
        ],
        [
            'name' => 'Soulevé de terre',
            'description' => 'Mouvement de tirage depuis le sol.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg', 'perceived_effort'],
        ],
        [
            'name' => 'Fentes',
            'description' => 'Travail unilatéral des jambes.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg'],
        ],
        [
            'name' => 'Hip thrust',
            'description' => 'Extension des hanches avec charge.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg'],
        ],
        [
            'name' => 'Mollets debout',
            'description' => 'Extension des chevilles avec charge.',
            'category' => 'force',
            'metrics' => ['sets', 'repetitions', 'weight_kg'],
        ],
        [
            'name' => 'Gainage planche',
            'description' => 'Maintien statique du tronc.',
            'category' => 'core',
            'metrics' => ['duration_minutes', 'perceived_effort'],
        ],
        [
            'name' => 'Gainage latéral',
            'description' => 'Maintien latéral du tronc.',
            'category' => 'core',
            'metrics' => ['duration_minutes', 'perceived_effort'],
        ],
        [
            'name' => 'Crunch',
            'description' => 'Flexion contrôlée du tronc.',
            'category' => 'core',
            'metrics' => ['sets', 'repetitions'],
        ],
        [
            'name' => 'Relevés de jambes',
            'description' => 'Élévation des jambes pour le tronc.',
            'category' => 'core',
            'metrics' => ['sets', 'repetitions'],
        ],
    ],
];
