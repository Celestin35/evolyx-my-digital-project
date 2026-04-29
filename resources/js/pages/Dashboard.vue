<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import WeightChart from '@/components/WeightChart.vue';

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

const props = defineProps<{
    weightEntries: WeightEntry[];
    recentPerformedSessions: RecentPerformedSession[];
    recentPerformances: RecentPerformance[];
}>();

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
            };
        }),
);

const formattedRecentPerformances = computed(() =>
    props.recentPerformances.map((performance) => ({
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
        details.push(`${performance.weight.toFixed(2)} kg`);
    }

    if (performance.repetitions !== null) {
        details.push(`${performance.repetitions} rep`);
    }

    if (performance.duration_minutes !== null) {
        details.push(`${performance.duration_minutes.toFixed(2)} min`);
    }

    if (performance.distance_meters !== null) {
        details.push(`${performance.distance_meters.toFixed(0)} m`);
    }

    return details.length > 0 ? details.join(' - ') : 'Performance renseignee';
};
</script>

<template>
    <Head title="Tableau de bord" />

    <AppLayout
        title="Tableau de bord"
        subtitle="Bienvenue sur votre tableau de bord personnel !"
    >
        <section
            class="grid h-full min-h-0 grid-cols-1 gap-4 lg:grid-cols-[1.05fr_0.95fr]"
        >
            <div class="flex min-h-[360px] flex-col rounded-lg bg-white p-4">
                <h2 class="mb-4 text-xl font-bold">Suivi du poids</h2>
                <div class="min-h-0 flex-1">
                    <WeightChart :weight-entries="weightEntries" />
                </div>
            </div>

            <div class="rounded-lg bg-white p-4 lg:col-start-2 lg:row-span-2">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold">Dernieres seances</h2>
                        <p class="mt-1 text-sm text-neutral-600">
                            Frise des dernieres validations.
                        </p>
                    </div>
                    <div
                        class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700"
                    >
                        {{ formattedRecentSessions.length }} validee(s)
                    </div>
                </div>

                <div
                    v-if="formattedRecentSessions.length > 0"
                    class="mt-6 overflow-x-auto pb-2"
                >
                    <div
                        class="relative grid min-w-[680px] gap-3"
                        :style="{
                            gridTemplateColumns: `repeat(${formattedRecentSessions.length}, minmax(0, 1fr))`,
                        }"
                    >
                        <div
                            class="absolute top-7 right-[8%] left-[8%] h-px bg-neutral-200"
                        ></div>

                        <div
                            v-for="session in formattedRecentSessions"
                            :key="session.id"
                            class="relative flex min-w-0 flex-col items-center text-center"
                        >
                            <div
                                class="z-10 flex h-14 w-14 flex-col items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 text-emerald-800"
                            >
                                <span class="text-base leading-none font-bold">
                                    {{ session.dayLabel }}
                                </span>
                                <span
                                    class="mt-1 text-[10px] leading-none uppercase"
                                >
                                    {{ session.monthLabel }}
                                </span>
                            </div>

                            <div
                                class="mt-3 w-full rounded-lg border border-neutral-200 p-3"
                            >
                                <p class="truncate text-sm font-semibold">
                                    {{ session.workout_session_name }}
                                </p>
                                <p class="mt-1 text-xs text-neutral-500">
                                    {{ session.fullDateLabel }}
                                </p>
                                <p class="mt-2 text-xs text-emerald-700">
                                    {{ session.performances_count }}
                                    performance(s)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <p v-else class="mt-6 text-sm text-neutral-600">
                    Aucune seance recente.
                </p>
            </div>

            <div class="rounded-lg bg-white p-4 lg:col-start-1 lg:row-start-2">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold">Dernieres performances</h2>
                    <div
                        class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-700"
                    >
                        {{ formattedRecentPerformances.length }} entree(s)
                    </div>
                </div>

                <div
                    v-if="formattedRecentPerformances.length > 0"
                    class="mt-4 space-y-3"
                >
                    <div
                        v-for="performance in formattedRecentPerformances"
                        :key="performance.id"
                        class="rounded-lg border border-neutral-200 p-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold">
                                    {{ performance.exercise_name }}
                                </p>
                                <p class="mt-1 text-sm text-neutral-600">
                                    {{ performance.sport_name ?? 'Sport' }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm text-neutral-500">
                                {{ performance.dateLabel }}
                            </p>
                        </div>
                        <p class="mt-2 text-sm text-evo-black">
                            {{ formatPerformanceDetails(performance) }}
                        </p>
                    </div>
                </div>

                <p v-else class="mt-4 text-sm text-neutral-600">
                    Aucune performance recente.
                </p>
            </div>
        </section>
    </AppLayout>
</template>
