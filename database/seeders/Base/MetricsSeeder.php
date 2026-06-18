<?php

namespace Database\Seeders\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetricsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $metrics = [
            ['key' => 'sets', 'label' => 'Séries', 'unit' => null, 'value_type' => 'integer'],
            ['key' => 'repetitions', 'label' => 'Répétitions', 'unit' => 'rep', 'value_type' => 'integer'],
            ['key' => 'weight_kg', 'label' => 'Charge', 'unit' => 'kg', 'value_type' => 'decimal'],
            ['key' => 'duration_minutes', 'label' => 'Durée', 'unit' => 'min', 'value_type' => 'decimal'],
            ['key' => 'distance_meters', 'label' => 'Distance', 'unit' => 'm', 'value_type' => 'decimal'],
            ['key' => 'pace_min_per_km', 'label' => 'Allure', 'unit' => 'min/km', 'value_type' => 'decimal'],
            ['key' => 'speed_kmh', 'label' => 'Vitesse', 'unit' => 'km/h', 'value_type' => 'decimal'],
            ['key' => 'elevation_gain_meters', 'label' => 'Dénivelé positif', 'unit' => 'm', 'value_type' => 'decimal'],
            ['key' => 'heart_rate_bpm', 'label' => 'Fréquence cardiaque', 'unit' => 'bpm', 'value_type' => 'integer'],
            ['key' => 'perceived_effort', 'label' => 'Effort ressenti', 'unit' => '/10', 'value_type' => 'integer'],
            ['key' => 'cadence_rpm', 'label' => 'Cadence de pédalage', 'unit' => 'rpm', 'value_type' => 'integer'],
            ['key' => 'power_watts', 'label' => 'Puissance', 'unit' => 'W', 'value_type' => 'integer'],
            ['key' => 'stroke_count', 'label' => 'Nombre de mouvements', 'unit' => 'mouvements', 'value_type' => 'integer'],
            ['key' => 'pool_length_seconds', 'label' => 'Temps par longueur', 'unit' => 'secondes', 'value_type' => 'decimal'],
            ['key' => 'successful_attempts', 'label' => 'Réussites', 'unit' => null, 'value_type' => 'integer'],
            ['key' => 'total_attempts', 'label' => 'Tentatives', 'unit' => null, 'value_type' => 'integer'],
        ];

        foreach ($metrics as $index => $metric) {
            DB::table('metrics')->updateOrInsert(
                ['key' => $metric['key']],
                [
                    'label' => $metric['label'],
                    'unit' => $metric['unit'],
                    'value_type' => $metric['value_type'],
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
}
