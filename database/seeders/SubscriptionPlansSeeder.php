<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('subscription_plans')->upsert([
            [
                'name' => 'Free',
                'price' => 0.00,
                'ads_enabled' => true,
                'premium_features' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Essential',
                'price' => 3.99,
                'ads_enabled' => false,
                'premium_features' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Premium',
                'price' => 14.99,
                'ads_enabled' => false,
                'premium_features' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['name'], ['price', 'ads_enabled', 'premium_features', 'updated_at']);
    }
}
