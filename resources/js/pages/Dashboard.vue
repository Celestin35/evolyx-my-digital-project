<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdInlineSlot from '@/components/ads/AdInlineSlot.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import WeightChart from '@/components/WeightChart.vue';
import { Button } from '@/components/ui/button';
import { useAds } from '@/composables/useAds';

type WeightEntry = {
    id: number;
    weight: number;
    body_fat: number | null;
    created_at: string | null;
};

type RecentPerformedSession = {
    id: number;
    workout_session_name: string;
    performed_at: string | null;
    completed_at: string | null;
    performances_count: number;
    notes: string | null;
};

type RecentPerformance = {
    id: number;
    performed_at: string | null;
    weight: number | null;
    repetitions: number | null;
    duration_minutes: number | null;
    distance_meters: number | null;
    exercise_name: string;
    sport_name: string | null;
};

type CommunityFeedPost = {
    id: number;
    author_name: string;
    title: string;
    workout_session_name: string;
    published_at: string | null;
};

const props = defineProps<{
    weightEntries: WeightEntry[];
    recentPerformedSessions: RecentPerformedSession[];
    recentPerformances: RecentPerformance[];
    canAccessCommunity: boolean;
    communityFeed: CommunityFeedPost[];
}>();

const ads = useAds();

const formatRelativeDayLabel = (date: Date | null) => {
    if (!date) {
        return '';
    }

    const today = new Date();
    const sessionDate = new Date(date);

    today.setHours(0, 0, 0, 0);
    sessionDate.setHours(0, 0, 0, 0);

    const daysDiff = Math.round(
        (today.getTime() - sessionDate.getTime()) / (1000 * 60 * 60 * 24),
    );

    if (daysDiff <= 0) {
        return "aujourd'hui";
    }

    if (daysDiff === 1) {
        return 'hier';
    }

    return `il y a ${daysDiff} jours`;
};

const formattedRecentSessions = computed(() =>
    [...props.recentPerformedSessions]
        .sort((firstSession, secondSession) => {
            return (
                new Date(secondSession.performed_at ?? 0).getTime() -
                new Date(firstSession.performed_at ?? 0).getTime()
            );
        })
        .map((session) => {
            const date = session.performed_at
                ? new Date(session.performed_at)
                : null;

            return {
                ...session,
                dayLabel: date
                    ? new Intl.DateTimeFormat('fr-FR', {
                          day: '2-digit',
                      }).format(date)
                    : '--',
                monthLabel: date
                    ? new Intl.DateTimeFormat('fr-FR', {
                          month: 'short',
                      }).format(date)
                    : '',
                fullDateLabel: date
                    ? new Intl.DateTimeFormat('fr-FR', {
                          day: 'numeric',
                          month: 'long',
                      }).format(date)
                    : '-',
                relativeDateLabel: formatRelativeDayLabel(date),
            };
        })
        .slice(0, 4),
);

const formattedRecentPerformances = computed(() =>
    [...props.recentPerformances]
        .sort(
            (firstPerformance, secondPerformance) =>
                new Date(secondPerformance.performed_at ?? 0).getTime() -
                new Date(firstPerformance.performed_at ?? 0).getTime(),
        )
        .slice(0, 3)
        .map((performance) => ({
            ...performance,
            dateLabel: performance.performed_at
                ? new Intl.DateTimeFormat('fr-FR', {
                      day: 'numeric',
                      month: 'short',
                  }).format(new Date(performance.performed_at))
                : '-',
        })),
);

const formatPerformanceDetails = (performance: RecentPerformance) => {
    const details = [];

    if (performance.weight !== null) {
        details.push(`${Number(performance.weight.toFixed(2))}kg`);
    }

    if (performance.repetitions !== null) {
        details.push(`${performance.repetitions} rep`);
    }

    if (performance.duration_minutes !== null) {
        details.push(`${Number(performance.duration_minutes.toFixed(2))}min`);
    }

    if (performance.distance_meters !== null) {
        const distance =
            performance.distance_meters >= 1000
                ? `${Number((performance.distance_meters / 1000).toFixed(2))}km`
                : `${performance.distance_meters.toFixed(0)}m`;

        details.push(distance);
    }

    return details.length > 0 ? details.join(' | ') : 'Performance renseignee';
};

const formatCommunityPostDate = (date: string | null) => {
    if (!date) {
        return '';
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
};
</script>

<template>
    <Head title="Tableau de bord" />

    <AppLayout
        title="Tableau de bord"
        subtitle="Bienvenue sur votre tableau de bord personnel !"
    >
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2 max-lg:pb-23">
            <div class="order-3 flex min-h-90 flex-col rounded-lg bg-evo-white p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold">Suivi du poids</h2>
                    <Button :as="Link" href="/progress" class="px-3 py-1.5 text-sm">
                        Mon poids
                    </Button>
                </div>
                <div class="min-h-0 flex-1">
                    <WeightChart :weight-entries="weightEntries" />
                </div>
            </div>

            <div class="order-3 min-h-90 rounded-lg bg-evo-white p-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold">Dernieres performances</h2>
                    <Button :as="Link" href="/progress" class="px-3 py-1.5 text-sm">
                        Mes performances
                    </Button>
                </div>

                <div
                    v-if="formattedRecentPerformances.length > 0"
                    class="mt-4 space-y-3"
                >
                    <div
                        v-for="performance in formattedRecentPerformances"
                        :key="performance.id"
                        class="grid gap-3 rounded-lg border border-neutral-300 bg-evo-gray p-3 sm:grid-cols-[1fr_auto] sm:items-center"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-semibold">
                                {{ performance.exercise_name }}
                            </p>
                            <p class="mt-1 text-xs text-neutral-600">
                                {{ performance.sport_name ?? 'Sport' }}
                                <span v-if="performance.dateLabel">
                                    | {{ performance.dateLabel }}
                                </span>
                            </p>
                        </div>
                        <p
                            class="rounded-lg border border-evo-orange bg-evo-white px-4 py-3 text-center text-sm font-medium text-evo-orange sm:min-w-40"
                        >
                            {{ formatPerformanceDetails(performance) }}
                        </p>
                    </div>
                </div>

                <p v-else class="mt-4 text-sm text-neutral-600">
                    Aucune performance récente.
                </p>
            </div>

            <AdInlineSlot
                :enabled="ads.enabled"
                class="order-2 lg:col-span-2"
            />

            <div class="order-2 rounded-lg bg-evo-white p-4 lg:col-span-2">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold">Feed communautaire</h2>
                        <p class="hidden lg:block mt-1 text-sm text-neutral-600">
                            Les dernières publications des membres que vous
                            suivez.
                        </p>
                    </div>
                    <Button :as="Link" href="/community">Ouvrir</Button>
                </div>

                <div
                    v-if="canAccessCommunity && communityFeed.length > 0"
                    class="mt-4 grid gap-3 md:grid-cols-3"
                >
                    <Link
                        v-for="post in communityFeed"
                        :key="post.id"
                        href="/community"
                        class="rounded-lg border border-neutral-300 bg-evo-gray p-4 transition hover:border-evo-orange"
                    >
                        <p class="text-xs text-neutral-600">
                            {{ post.author_name }}
                            <span v-if="post.published_at">
                                | {{ formatCommunityPostDate(post.published_at) }}
                            </span>
                        </p>
                        <h3 class="mt-2 font-semibold">
                            {{ post.title }}
                        </h3>
                        <p class="mt-2 text-sm text-neutral-700">
                            {{ post.workout_session_name }}
                        </p>
                    </Link>
                </div>

                <div
                    v-else
                    class="mt-4 rounded-lg border border-neutral-300 bg-evo-gray p-4"
                >
                    <p class="font-semibold">
                        {{
                            canAccessCommunity
                                ? 'Votre feed est vide'
                                : 'Feed réservé au Premium'
                        }}
                    </p>
                    <p class="mt-1 text-sm text-neutral-600">
                        {{
                            canAccessCommunity
                                ? 'Suivez des membres depuis la page Communauté pour remplir ce bloc.'
                                : 'Passez Premium pour suivre des membres et voir leurs séances partagées.'
                        }}
                    </p>
                </div>
            </div>

            <div class="order-1 rounded-lg bg-evo-white p-4 lg:col-span-2">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold">Dernières séances</h2>
                    </div>
                    <Button :as="Link" href="/sessions" class="px-3 py-1.5 text-sm">
                        Mes séances
                    </Button>
                </div>

                <div
                    v-if="formattedRecentSessions.length > 0"
                    class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        v-for="session in formattedRecentSessions"
                        :key="session.id"
                        class="min-w-0 rounded-lg border border-neutral-300 bg-evo-gray p-4"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg border border-evo-orange bg-evo-white text-evo-orange"
                            >
                                <span class="text-lg leading-none font-bold">
                                    {{ session.dayLabel }}
                                </span>
                                <span class="mt-1 text-[10px] uppercase">
                                    {{ session.monthLabel }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold">
                                    {{ session.workout_session_name }}
                                </p>
                                <p
                                    v-if="session.relativeDateLabel"
                                    class="mt-1 text-xs text-neutral-500"
                                >
                                    ({{ session.relativeDateLabel }})
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <p v-else class="mt-4 text-sm text-neutral-600">
                    Aucune séance récente.
                </p>
            </div>
        </section>
    </AppLayout>
</template>
