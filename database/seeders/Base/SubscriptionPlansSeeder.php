<?php

namespace Database\Seeders\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('subscription_plans')->upsert([
            [
                'name' => 'Gratuit',
                'price' => 0.00,
                'ads_enabled' => true,
                'premium_features' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Plus',
                'price' => 4.99,
                'ads_enabled' => false,
                'premium_features' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Premium',
                'price' => 9.99,
                'ads_enabled' => false,
                'premium_features' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['name'], ['price', 'ads_enabled', 'premium_features', 'updated_at']);

        $this->replaceLegacyPlan('Free', 'Gratuit', $now);
        $this->replaceLegacyPlan('Essential', 'Plus', $now);
    }

    private function replaceLegacyPlan(string $legacyName, string $targetName, $now): void
    {
        $legacyPlanId = DB::table('subscription_plans')->where('name', $legacyName)->value('id');
        $targetPlanId = DB::table('subscription_plans')->where('name', $targetName)->value('id');

        if (! $legacyPlanId || ! $targetPlanId) {
            return;
        }

        DB::table('subscriptions')
            ->where('subscription_plan_id', $legacyPlanId)
            ->update([
                'subscription_plan_id' => $targetPlanId,
                'updated_at' => $now,
            ]);

        DB::table('subscription_plans')
            ->where('id', $legacyPlanId)
            ->delete();
    }
}
