<?php

namespace Database\Seeders\Demo\MainUser;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeightEntriesSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id', 'email');

        $demoUserId = $users['demo@evolyx.local'] ?? null;

        if (! $demoUserId) {
            return;
        }

        DB::table('weight_entries')->where('user_id', $demoUserId)->delete();

        mt_srand(20260415);

        $baseWeight = 69.40;
        $baseBodyFat = 27.40;
        $trendWeight = -0.68;
        $trendBodyFat = -0.28;

        $entries = [];

        for ($monthOffset = 7; $monthOffset >= 0; $monthOffset--) {
            $monthDate = now()->startOfMonth()->subMonths($monthOffset);
            $entriesThisMonth = mt_rand(2, 4);

            for ($entryIndex = 0; $entryIndex < $entriesThisMonth; $entryIndex++) {
                $day = min(
                    mt_rand(3 + ($entryIndex * 6), 8 + ($entryIndex * 7)),
                    $monthDate->daysInMonth
                );

                $recordedAt = $monthDate->copy()
                    ->day($day)
                    ->setTime(mt_rand(6, 9), mt_rand(0, 59), 0);

                $progressIndex = (7 - $monthOffset) + ($entryIndex / max($entriesThisMonth, 1));
                $weightNoise = mt_rand(-18, 22) / 100;
                $bodyFatNoise = mt_rand(-12, 14) / 100;

                $weight = round($baseWeight + ($progressIndex * $trendWeight) + $weightNoise, 2);
                $bodyFat = round($baseBodyFat + ($progressIndex * $trendBodyFat) + $bodyFatNoise, 2);

                $entries[] = [
                    'user_id' => $demoUserId,
                    'weight' => $weight,
                    'body_fat' => max($bodyFat, 12),
                    'created_at' => $recordedAt,
                    'updated_at' => $recordedAt,
                ];
            }
        }

        usort($entries, fn (array $left, array $right) => $left['created_at'] <=> $right['created_at']);

        foreach ($entries as $entry) {
            DB::table('weight_entries')->updateOrInsert(
                [
                    'user_id' => $entry['user_id'],
                    'created_at' => $entry['created_at'],
                ],
                [
                    'weight' => $entry['weight'],
                    'body_fat' => $entry['body_fat'],
                    'updated_at' => $entry['updated_at'],
                ]
            );
        }
    }
}
