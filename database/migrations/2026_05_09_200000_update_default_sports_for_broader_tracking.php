<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $sports = [
        'Marche / randonnée',
        'Fitness / musculation',
        'Vélo / cyclisme',
        'Course à pied / running',
        'Natation',
        'Football',
        'Sports de montagne',
        'Tennis / padel',
        'Basketball',
        'Yoga / Pilates',
        'Sports de glisse',
        'Arts martiaux / sports de combat',
        'Rugby',
        'Volleyball',
        'Badminton',
    ];

    private array $legacySports = [
        'Musculation' => 'Fitness / musculation',
        'Calisthenie' => 'Fitness / musculation',
        'Yoga' => 'Yoga / Pilates',
        'Pilates' => 'Yoga / Pilates',
        'Mobilite' => 'Yoga / Pilates',
        'Meditation' => 'Yoga / Pilates',
        'Cardio doux' => 'Marche / randonnée',
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->sports as $sportName) {
            DB::table('sports')->updateOrInsert(
                ['name' => $sportName],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }

        foreach ($this->legacySports as $legacyName => $targetName) {
            $legacyId = DB::table('sports')->where('name', $legacyName)->value('id');
            $targetId = DB::table('sports')->where('name', $targetName)->value('id');

            if (! $legacyId || ! $targetId || $legacyId === $targetId) {
                continue;
            }

            DB::table('exercises')
                ->where('sport_id', $legacyId)
                ->update(['sport_id' => $targetId, 'updated_at' => $now]);

            $userIds = DB::table('sport_user')
                ->where('sport_id', $legacyId)
                ->pluck('user_id');

            foreach ($userIds as $userId) {
                DB::table('sport_user')->insertOrIgnore([
                    'user_id' => $userId,
                    'sport_id' => $targetId,
                ]);
            }

            DB::table('sport_user')->where('sport_id', $legacyId)->delete();
            DB::table('sports')->where('id', $legacyId)->delete();
        }
    }

    public function down(): void
    {
        //
    }
};
