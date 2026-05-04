<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PerformanceChart from '@/components/PerformanceChart.vue';
import WeightChart from '@/components/WeightChart.vue';

type WeightEntry = {
    id: number;
    weight: number;
    body_fat: number | null;
    created_at: string | null;
};

type ProgressRange = '1m' | '3m' | '6m' | '1y' | 'all';
type PerformanceMetric =
    | 'weight'
    | 'repetitions'
    | 'duration_minutes'
    | 'distance_meters'
    | 'volume';

type PerformanceEntry = {
    id: number;
    performed_at: string | null;
    weight: number | null;
    repetitions: number | null;
    duration_minutes: number | null;
    distance_meters: number | null;
    exercise_id: number;
    exercise_name: string;
    sport_id: number | null;
    sport_name: string | null;
};

type ProgressPageProps = {
    flash?: {
        success?: string;
    };
};

const props = defineProps<{
    weightEntries: WeightEntry[];
    performances: PerformanceEntry[];
}>();

const page = usePage<ProgressPageProps>();

const selectedRange = ref<ProgressRange>('3m');
const selectedPerformanceRange = ref<ProgressRange>('3m');
const selectedSportId = ref<number | 'all'>('all');
const selectedExerciseId = ref<number | null>(null);
const selectedMetric = ref<PerformanceMetric>('weight');
const rangeOptions: Array<{ value: ProgressRange; label: string }> = [
    { value: '1m', label: '1M' },
    { value: '3m', label: '3M' },
    { value: '6m', label: '6M' },
    { value: '1y', label: '1A' },
    { value: 'all', label: 'Tout' },
];
const metricOptions: Array<{
    value: PerformanceMetric;
    label: string;
    unit: string;
}> = [
    { value: 'weight', label: 'Charge', unit: 'kg' },
    { value: 'repetitions', label: 'Repetitions', unit: 'rep' },
    { value: 'volume', label: 'Volume', unit: 'kg' },
    { value: 'duration_minutes', label: 'Duree', unit: 'min' },
    { value: 'distance_meters', label: 'Distance', unit: 'm' },
];

const weightEntryForm = useForm({
    weight: '',
    body_fat: '',
});

const flashSuccessMessage = computed(() => page.props.flash?.success);

const getRangeCutoffDate = (range: ProgressRange) => {
    if (range === 'all') {
        return null;
    }

    const now = new Date();
    const cutoffDate = new Date(now);

    if (range === '1m') {
        cutoffDate.setMonth(now.getMonth() - 1);
    } else if (range === '3m') {
        cutoffDate.setMonth(now.getMonth() - 3);
    } else if (range === '6m') {
        cutoffDate.setMonth(now.getMonth() - 6);
    } else {
        cutoffDate.setFullYear(now.getFullYear() - 1);
    }

    return cutoffDate;
};

const filteredWeightEntries = computed(() => {
    const cutoffDate = getRangeCutoffDate(selectedRange.value);

    if (!cutoffDate) {
        return props.weightEntries;
    }

    return props.weightEntries.filter((entry) => {
        if (!entry.created_at) {
            return false;
        }

        return new Date(entry.created_at) >= cutoffDate;
    });
});

const sportsWithPerformances = computed(() => {
    const sports = new Map<number, string>();

    props.performances.forEach((performance) => {
        if (performance.sport_id === null || !performance.sport_name) {
            return;
        }

        sports.set(performance.sport_id, performance.sport_name);
    });

    return [...sports.entries()]
        .map(([id, name]) => ({ id, name }))
        .sort((firstSport, secondSport) =>
            firstSport.name.localeCompare(secondSport.name),
        );
});

const performancesForSelectedSport = computed(() => {
    if (selectedSportId.value === 'all') {
        return props.performances;
    }

    return props.performances.filter(
        (performance) => performance.sport_id === selectedSportId.value,
    );
});

const exercisesWithPerformances = computed(() => {
    const exercises = new Map<number, string>();

    performancesForSelectedSport.value.forEach((performance) => {
        exercises.set(performance.exercise_id, performance.exercise_name);
    });

    return [...exercises.entries()]
        .map(([id, name]) => ({ id, name }))
        .sort((firstExercise, secondExercise) =>
            firstExercise.name.localeCompare(secondExercise.name),
        );
});

const selectedExercisePerformances = computed(() => {
    const fallbackExerciseId = exercisesWithPerformances.value[0]?.id ?? null;
    const exerciseId = selectedExerciseId.value ?? fallbackExerciseId;

    if (exerciseId === null) {
        return [];
    }

    return performancesForSelectedSport.value.filter(
        (performance) => performance.exercise_id === exerciseId,
    );
});

const availableMetricOptions = computed(() =>
    metricOptions.filter((metric) =>
        selectedExercisePerformances.value.some((performance) => {
            if (metric.value === 'volume') {
                return (
                    performance.weight !== null &&
                    performance.repetitions !== null
                );
            }

            return performance[metric.value] !== null;
        }),
    ),
);

const activeMetric = computed(() => {
    return (
        availableMetricOptions.value.find(
            (metric) => metric.value === selectedMetric.value,
        ) ??
        availableMetricOptions.value[0] ??
        metricOptions[0]
    );
});

const filteredPerformanceEntries = computed(() => {
    const cutoffDate = getRangeCutoffDate(selectedPerformanceRange.value);

    return selectedExercisePerformances.value.filter((performance) => {
        if (!performance.performed_at) {
            return false;
        }

        if (!cutoffDate) {
            return true;
        }

        return new Date(performance.performed_at) >= cutoffDate;
    });
});

const chartPerformanceEntries = computed(() =>
    filteredPerformanceEntries.value.filter((performance) => {
        if (activeMetric.value.value === 'volume') {
            return (
                performance.weight !== null && performance.repetitions !== null
            );
        }

        return performance[activeMetric.value.value] !== null;
    }),
);

const chartKey = computed(() => {
    const lastEntryId = filteredWeightEntries.value.at(-1)?.id ?? 'none';
    return `${selectedRange.value}-${filteredWeightEntries.value.length}-${lastEntryId}`;
});
const performanceChartKey = computed(() => {
    const lastEntryId = chartPerformanceEntries.value.at(-1)?.id ?? 'none';

    return [
        selectedPerformanceRange.value,
        selectedSportId.value,
        selectedExerciseId.value ?? 'first',
        activeMetric.value.value,
        chartPerformanceEntries.value.length,
        lastEntryId,
    ].join('-');
});

const recentPerformances = computed(() =>
    [...props.performances]
        .filter((performance) => performance.performed_at)
        .sort(
            (firstPerformance, secondPerformance) =>
                new Date(secondPerformance.performed_at as string).getTime() -
                new Date(firstPerformance.performed_at as string).getTime(),
        )
        .slice(0, 5)
        .map((performance) => ({
            ...performance,
            dateLabel: new Intl.DateTimeFormat('fr-FR', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            }).format(new Date(performance.performed_at as string)),
        })),
);

const formatPerformanceValue = (
    performance: PerformanceEntry,
    metric: PerformanceMetric,
) => {
    if (metric === 'volume') {
        if (performance.weight === null || performance.repetitions === null) {
            return null;
        }

        return `${(performance.weight * performance.repetitions).toFixed(2)} kg`;
    }

    const value = performance[metric];

    if (value === null) {
        return null;
    }

    const metricOption = metricOptions.find(
        (option) => option.value === metric,
    );

    return `${Number(value).toFixed(metric === 'repetitions' ? 0 : 2)} ${
        metricOption?.unit ?? ''
    }`;
};

const onSportChange = () => {
    selectedExerciseId.value = null;
};

const onExerciseChange = () => {
    if (
        availableMetricOptions.value.length > 0 &&
        !availableMetricOptions.value.some(
            (metric) => metric.value === selectedMetric.value,
        )
    ) {
        selectedMetric.value = availableMetricOptions.value[0].value;
    }
};

watch(availableMetricOptions, (metrics) => {
    if (
        metrics.length > 0 &&
        !metrics.some((metric) => metric.value === selectedMetric.value)
    ) {
        selectedMetric.value = metrics[0].value;
    }
});

const submitWeightEntry = () => {
    weightEntryForm.post('/progress/weight-entries', {
        preserveScroll: true,
        onSuccess: () => {
            weightEntryForm.reset('body_fat');
        },
    });
};
</script>

<template>
    <Head title="Progression" />

    <AppLayout
        title="Progression"
        subtitle="Suivez votre poids et ajoutez vos nouvelles mesures."
    >
        <section class="grid gap-4 xl:grid-cols-2">
            <div class="space-y-4">
                <section class="rounded-lg bg-white p-6">
                    <div
                        class="flex flex-wrap items-center justify-between gap-3"
                    >
                        <div>
                            <h2 class="text-lg font-semibold">
                                Courbe de poids
                            </h2>
                            <p class="mt-1 text-sm text-neutral-600">
                                Suivi dedie aux mesures corporelles.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="option in rangeOptions"
                                :key="option.value"
                                type="button"
                                class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="
                                    selectedRange === option.value
                                        ? 'border-evo-black bg-evo-black text-evo-white'
                                        : 'border-neutral-300 text-evo-black hover:bg-neutral-100'
                                "
                                @click="selectedRange = option.value"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 h-[320px]">
                        <WeightChart
                            v-if="filteredWeightEntries.length > 0"
                            :key="chartKey"
                            :weight-entries="filteredWeightEntries"
                        />

                        <div
                            v-else
                            class="flex h-full items-center justify-center rounded-lg border border-dashed border-neutral-300 text-sm text-neutral-500"
                        >
                            Aucune entree disponible sur cette periode.
                        </div>
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6">
                    <h2 class="text-lg font-semibold">
                        Ajouter une entree de poids
                    </h2>

                    <p class="mt-2 text-sm text-neutral-600">
                        La masse grasse est optionnelle.
                    </p>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="entry_weight" class="block font-medium">
                                Poids (kg)
                            </label>
                            <input
                                id="entry_weight"
                                v-model="weightEntryForm.weight"
                                type="number"
                                min="20"
                                max="500"
                                step="0.01"
                                class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                            />
                            <p
                                v-if="weightEntryForm.errors.weight"
                                class="text-sm text-red-600"
                            >
                                {{ weightEntryForm.errors.weight }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label
                                for="entry_body_fat"
                                class="block font-medium"
                            >
                                Masse grasse (%)
                            </label>
                            <input
                                id="entry_body_fat"
                                v-model="weightEntryForm.body_fat"
                                type="number"
                                min="2"
                                max="75"
                                step="0.01"
                                class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                            />
                            <p
                                v-if="weightEntryForm.errors.body_fat"
                                class="text-sm text-red-600"
                            >
                                {{ weightEntryForm.errors.body_fat }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:opacity-90 disabled:opacity-50"
                            :disabled="weightEntryForm.processing"
                            @click="submitWeightEntry"
                        >
                            {{
                                weightEntryForm.processing
                                    ? 'Enregistrement...'
                                    : 'Ajouter l entree'
                            }}
                        </button>
                        <p
                            v-if="
                                weightEntryForm.recentlySuccessful ||
                                flashSuccessMessage
                            "
                            class="text-sm text-emerald-700"
                        >
                            {{ flashSuccessMessage ?? 'Entree enregistree.' }}
                        </p>
                    </div>
                </section>
            </div>

            <div class="space-y-4">
                <section class="rounded-lg bg-white p-6">
                    <div
                        class="flex flex-wrap items-start justify-between gap-3"
                    >
                        <div>
                            <h2 class="text-lg font-semibold">
                                Graphique de performance
                            </h2>
                            <p class="mt-1 text-sm text-neutral-600">
                                Les metriques disponibles dependent de
                                l'exercice.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        <div class="space-y-2">
                            <label
                                for="performance_sport"
                                class="block font-medium"
                            >
                                Sport
                            </label>
                            <select
                                id="performance_sport"
                                v-model="selectedSportId"
                                class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                                @change="onSportChange"
                            >
                                <option value="all">Tous les sports</option>
                                <option
                                    v-for="sport in sportsWithPerformances"
                                    :key="sport.id"
                                    :value="sport.id"
                                >
                                    {{ sport.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label
                                for="performance_exercise"
                                class="block font-medium"
                            >
                                Exercice
                            </label>
                            <select
                                id="performance_exercise"
                                v-model="selectedExerciseId"
                                class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                                @change="onExerciseChange"
                            >
                                <option :value="null">
                                    Premier exercice disponible
                                </option>
                                <option
                                    v-for="exercise in exercisesWithPerformances"
                                    :key="exercise.id"
                                    :value="exercise.id"
                                >
                                    {{ exercise.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label
                                for="performance_metric"
                                class="block font-medium"
                            >
                                Performance
                            </label>
                            <select
                                id="performance_metric"
                                v-model="selectedMetric"
                                class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                            >
                                <option
                                    v-for="metric in availableMetricOptions"
                                    :key="metric.value"
                                    :value="metric.value"
                                >
                                    {{ metric.label }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <p class="font-medium">Periode</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="option in rangeOptions"
                                    :key="option.value"
                                    type="button"
                                    class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                    :class="
                                        selectedPerformanceRange ===
                                        option.value
                                            ? 'border-evo-black bg-evo-black text-evo-white'
                                            : 'border-neutral-300 text-evo-black hover:bg-neutral-100'
                                    "
                                    @click="
                                        selectedPerformanceRange = option.value
                                    "
                                >
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 h-[320px]">
                        <PerformanceChart
                            v-if="chartPerformanceEntries.length > 0"
                            :key="performanceChartKey"
                            :performances="chartPerformanceEntries"
                            :metric="activeMetric.value"
                            :metric-label="activeMetric.label"
                            :metric-unit="activeMetric.unit"
                        />

                        <div
                            v-else
                            class="flex h-full items-center justify-center rounded-lg border border-dashed border-neutral-300 px-4 text-center text-sm text-neutral-500"
                        >
                            Aucune performance disponible avec ces filtres.
                        </div>
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6">
                    <h2 class="text-lg font-semibold">
                        Dernieres performances
                    </h2>

                    <div
                        v-if="recentPerformances.length > 0"
                        class="mt-4 space-y-3"
                    >
                        <div
                            v-for="performance in recentPerformances"
                            :key="performance.id"
                            class="rounded-lg border border-neutral-200 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">
                                        {{ performance.exercise_name }}
                                    </p>
                                    <p class="mt-1 text-sm text-neutral-600">
                                        {{ performance.sport_name ?? 'Sport' }}
                                    </p>
                                </div>
                                <p class="text-sm text-neutral-500">
                                    {{ performance.dateLabel }}
                                </p>
                            </div>
                            <div
                                class="mt-3 flex flex-wrap gap-2 text-xs text-neutral-600"
                            >
                                <span
                                    v-if="
                                        formatPerformanceValue(
                                            performance,
                                            'weight',
                                        )
                                    "
                                    class="rounded-full bg-neutral-100 px-2 py-1"
                                >
                                    Charge:
                                    {{
                                        formatPerformanceValue(
                                            performance,
                                            'weight',
                                        )
                                    }}
                                </span>
                                <span
                                    v-if="
                                        formatPerformanceValue(
                                            performance,
                                            'repetitions',
                                        )
                                    "
                                    class="rounded-full bg-neutral-100 px-2 py-1"
                                >
                                    Reps:
                                    {{
                                        formatPerformanceValue(
                                            performance,
                                            'repetitions',
                                        )
                                    }}
                                </span>
                                <span
                                    v-if="
                                        formatPerformanceValue(
                                            performance,
                                            'duration_minutes',
                                        )
                                    "
                                    class="rounded-full bg-neutral-100 px-2 py-1"
                                >
                                    Duree:
                                    {{
                                        formatPerformanceValue(
                                            performance,
                                            'duration_minutes',
                                        )
                                    }}
                                </span>
                                <span
                                    v-if="
                                        formatPerformanceValue(
                                            performance,
                                            'distance_meters',
                                        )
                                    "
                                    class="rounded-full bg-neutral-100 px-2 py-1"
                                >
                                    Distance:
                                    {{
                                        formatPerformanceValue(
                                            performance,
                                            'distance_meters',
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="mt-4 text-sm text-neutral-600">
                        Aucune performance enregistree pour le moment.
                    </p>
                </section>
            </div>
        </section>
    </AppLayout>
</template>
