<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $supportedSports = [
        'Fitness / musculation' => 'Musculation',
        'Course à pied / running' => 'Course à pied',
        'Vélo / cyclisme' => 'Vélo',
        'Natation' => 'Natation',
        'Marche / randonnée' => 'Marche',
        'Yoga' => 'Yoga',
        'Pilates' => 'Pilates',
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->supportedSports as $name => $displayName) {
            DB::table('sports')->updateOrInsert(
                ['name' => $name],
                ['display_name' => $displayName, 'created_at' => $now, 'updated_at' => $now],
            );
        }

        $this->moveSport('Musculation', 'Fitness / musculation');
        $this->moveSport('Calisthenie', 'Fitness / musculation');
        $this->moveSport('Cardio doux', 'Marche / randonnée');
        $this->moveSport('Mobilite', 'Yoga');
        $this->moveSport('Meditation', 'Yoga');
        $this->moveSport('Yoga', 'Yoga');
        $this->moveSport('Pilates', 'Pilates');
        $this->splitYogaPilates();

        $supportedIds = DB::table('sports')
            ->whereIn('name', array_keys($this->supportedSports))
            ->pluck('id');

        DB::table('sport_user')->whereNotIn('sport_id', $supportedIds)->delete();

        DB::table('sports')
            ->whereNotIn('id', $supportedIds)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('exercises')
                    ->whereColumn('exercises.sport_id', 'sports.id');
            })
            ->delete();
    }

    public function down(): void
    {
        //
    }

    private function moveSport(string $legacyName, string $targetName): void
    {
        $legacyId = DB::table('sports')->where('name', $legacyName)->value('id');
        $targetId = DB::table('sports')->where('name', $targetName)->value('id');

        if (! $legacyId || ! $targetId || $legacyId === $targetId) {
            return;
        }

        DB::table('exercises')->where('sport_id', $legacyId)->update([
            'sport_id' => $targetId,
            'updated_at' => now(),
        ]);

        $userIds = DB::table('sport_user')->where('sport_id', $legacyId)->pluck('user_id');

        foreach ($userIds as $userId) {
            DB::table('sport_user')->insertOrIgnore([
                'user_id' => $userId,
                'sport_id' => $targetId,
            ]);
        }

        DB::table('sport_user')->where('sport_id', $legacyId)->delete();
        DB::table('sports')->where('id', $legacyId)->delete();
    }

    private function splitYogaPilates(): void
    {
        $legacyId = DB::table('sports')->where('name', 'Yoga / Pilates')->value('id');
        $yogaId = DB::table('sports')->where('name', 'Yoga')->value('id');
        $pilatesId = DB::table('sports')->where('name', 'Pilates')->value('id');

        if (! $legacyId || ! $yogaId || ! $pilatesId) {
            return;
        }

        DB::table('exercises')
            ->where('sport_id', $legacyId)
            ->whereIn('name', ['Hundred', 'Pont de hanches'])
            ->update(['sport_id' => $pilatesId, 'updated_at' => now()]);

        DB::table('exercises')
            ->where('sport_id', $legacyId)
            ->update(['sport_id' => $yogaId, 'updated_at' => now()]);

        $userIds = DB::table('sport_user')->where('sport_id', $legacyId)->pluck('user_id');

        foreach ($userIds as $userId) {
            DB::table('sport_user')->insertOrIgnore(['user_id' => $userId, 'sport_id' => $yogaId]);
            DB::table('sport_user')->insertOrIgnore(['user_id' => $userId, 'sport_id' => $pilatesId]);
        }

        DB::table('sport_user')->where('sport_id', $legacyId)->delete();
        DB::table('sports')->where('id', $legacyId)->delete();
    }
};
