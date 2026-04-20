<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class WeightEntriesService
{
    public function getForUser(User $user): Collection
    {
        return $user->weightEntries()
            ->orderBy('created_at')
            ->get(['id', 'weight', 'body_fat', 'created_at'])
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'weight' => (float) $entry->weight,
                'body_fat' => $entry->body_fat !== null ? (float) $entry->body_fat : null,
                'created_at' => $entry->created_at?->toISOString(),
            ]);
    }
}
