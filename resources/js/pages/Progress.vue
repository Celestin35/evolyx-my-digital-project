<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AdInlineSlot from '@/components/ads/AdInlineSlot.vue';
import HorizontalTabs from '@/components/HorizontalTabs.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PerformanceChart from '@/components/PerformanceChart.vue';
import PremiumFeatureGate from '@/components/PremiumFeatureGate.vue';
import WeightChart from '@/components/WeightChart.vue';
import { Button } from '@/components/ui/button';
import { useAds } from '@/composables/useAds';

type WeightEntry = {
    id: number;
    weight: number;
    body_fat: number | null;
    created_at: string | null;
};

type ProgressRange = '1m' | '3m' | '6m' | '1y' | 'all';
type PerformanceMetric = string;

type MetricOption = {
    value: PerformanceMetric;
    label: string;
    unit: string;
    value_type?: 'decimal' | 'integer';
    sort_order?: number;
};

type PerformanceEntry = {
    id: number;
    performed_at: string | null;
    weight: number | null;
    repetitions: number | null;
    duration_minutes: number | null;
    distance_meters: number | null;
    available_metrics: Array<{
        key: string;
        label: string;
        unit: string | null;
        value_type: 'decimal' | 'integer';
        sort_order: number;
    }>;
    metric_values: Record<string, number>;
    exercise_id: number;
    exercise_name: string;
    sport_id: number | string | null;
    sport_name: string | null;
};

type SportOption = {
    id: number | string;
    name: string;
};

type ProgressPageProps = {
    flash?: {
        success?: string;
    };
};

const props = defineProps<{
    weightEntries: WeightEntry[];
    sports: SportOption[];
    performances: PerformanceEntry[];
    canViewPerformanceCharts: boolean;
    currentSubscriptionPlanName: string | null;
}>();

const page = usePage<ProgressPageProps>();
const ads = useAds();

const selectedRange = ref<ProgressRange>('3m');
const selectedPerformanceRange = ref<ProgressRange>('3m');
const activeProgressTab = ref<'weight' | 'performance'>('weight');
const progressTabs = [
    {
        value: 'weight',
        label: 'Évolution du poids',
    },
    {
        value: 'performance',
        label: 'Performances',
    },
] as const;
const selectedSportId = ref<number | string | 'all'>('all');
const selectedExerciseId = ref<number | null>(null);
const selectedMetric = ref<PerformanceMetric>('');
const normalizeId = (id: number | string) => Number(id);
const rangeOptions: Array<{ value: ProgressRange; label: string }> = [
    { value: '1m', label: '1M' },
    { value: '3m', label: '3M' },
    { value: '6m', label: '6M' },
    { value: '1y', label: '1A' },
    { value: 'all', label: 'Tout' },
];
const legacyMetricOptions: MetricOption[] = [
    { value: 'weight', label: 'Charge', unit: 'kg' },
    { value: 'repetitions', label: 'Répétitions', unit: 'rep' },
    { value: 'volume', label: 'Volume', unit: 'kg' },
    { value: 'duration_minutes', label: 'Durée', unit: 'min' },
    { value: 'distance_meters', label: 'Distance', unit: 'm' },
];

const weightEntryForm = useForm({
    weight: '',
    body_fat: '',
    entry_date: formatDateInput(new Date()),
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

const selectableSports = computed(() => {
    const sports = new Map<number, string>(
        props.sports.map((sport) => [normalizeId(sport.id), sport.name]),
    );

    props.performances.forEach((performance) => {
        if (performance.sport_id === null || !performance.sport_name) {
            return;
        }

        sports.set(normalizeId(performance.sport_id), performance.sport_name);
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
        (performance) =>
            performance.sport_id !== null &&
            normalizeId(performance.sport_id) ===
                normalizeId(selectedSportId.value),
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

const resolveMetricValue = (
    performance: PerformanceEntry,
    metric: PerformanceMetric,
) => {
    if (metric === 'volume') {
        const weight =
            performance.metric_values?.weight_kg ?? performance.weight;
        const repetitions =
            performance.metric_values?.repetitions ?? performance.repetitions;

        if (weight === null || repetitions === null) {
            return null;
        }

        return weight * repetitions;
    }

    const dynamicValue = performance.metric_values?.[metric];

    if (dynamicValue !== undefined && dynamicValue !== null) {
        return dynamicValue;
    }

    const legacyValues: Record<string, number | null> = {
        weight: performance.weight,
        weight_kg: performance.weight,
        repetitions: performance.repetitions,
        duration_minutes: performance.duration_minutes,
        distance_meters: performance.distance_meters,
    };

    return legacyValues[metric] ?? null;
};

const availableMetricOptions = computed<MetricOption[]>(() => {
    const options = new Map<string, MetricOption>();

    selectedExercisePerformances.value.forEach((performance) => {
        performance.available_metrics
            .filter(
                (metric) =>
                    resolveMetricValue(performance, metric.key) !== null,
            )
            .forEach((metric) => {
                options.set(metric.key, {
                    value: metric.key,
                    label: metric.label,
                    unit: metric.unit ?? '',
                    value_type: metric.value_type,
                    sort_order: metric.sort_order,
                });
            });
    });

    legacyMetricOptions
        .filter((metric) =>
            selectedExercisePerformances.value.some(
                (performance) =>
                    resolveMetricValue(performance, metric.value) !== null,
            ),
        )
        .forEach((metric) => {
            if (!options.has(metric.value)) {
                options.set(metric.value, metric);
            }
        });

    if (
        selectedExercisePerformances.value.some(
            (performance) => resolveMetricValue(performance, 'volume') !== null,
        )
    ) {
        options.set('volume', { value: 'volume', label: 'Volume', unit: 'kg' });
    }

    return [...options.values()].sort(
        (firstMetric, secondMetric) =>
            (firstMetric.sort_order ?? 999) -
            (secondMetric.sort_order ?? 999),
    );
});

const activeMetric = computed(() => {
    return (
        availableMetricOptions.value.find(
            (metric) => metric.value === selectedMetric.value,
        ) ??
        availableMetricOptions.value[0] ??
        legacyMetricOptions[0]
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
        return (
            resolveMetricValue(performance, activeMetric.value.value) !== null
        );
    }),
);

const chartKey = computed(() => {
    const lastEntryId = filteredWeightEntries.value.at(-1)?.id ?? 'none';
    return `${selectedRange.value}-${filteredWeightEntries.value.length}-${lastEntryId}`;
});

function formatDateInput(date: Date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const todayDate = computed(() => formatDateInput(new Date()));
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
    const value = resolveMetricValue(performance, metric);

    if (value === null) {
        return null;
    }

    const metricOption =
        availableMetricOptions.value.find(
            (option) => option.value === metric,
        ) ?? legacyMetricOptions.find((option) => option.value === metric);

    const decimals =
        metricOption?.value_type === 'integer' || metric === 'repetitions'
            ? 0
            : 2;

    return `${Number(value).toFixed(decimals)} ${metricOption?.unit ?? ''}`;
};

const visibleRecentMetricOptions = (performance: PerformanceEntry) => {
    const dynamicMetricOptions = performance.available_metrics
        .filter(
            (metric) => resolveMetricValue(performance, metric.key) !== null,
        )
        .map((metric) => ({
            value: metric.key,
            label: metric.label,
            unit: metric.unit ?? '',
            value_type: metric.value_type,
            sort_order: metric.sort_order,
        }));

    const fallbackOptions = legacyMetricOptions.filter(
        (metric) =>
            !dynamicMetricOptions.some(
                (option) => option.value === metric.value,
            ) && resolveMetricValue(performance, metric.value) !== null,
    );

    return [...dynamicMetricOptions, ...fallbackOptions]
        .sort(
            (firstMetric, secondMetric) =>
                (firstMetric.sort_order ?? 999) -
                (secondMetric.sort_order ?? 999),
        )
        .slice(0, 4);
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
            weightEntryForm.reset('weight', 'body_fat');
            weightEntryForm.entry_date = todayDate.value;
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
        <div class="space-y-4 max-lg:pb-23">
            <section class="rounded-lg bg-evo-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Fenêtres d'évolution
                        </h2>
                        <p class="mt-1 text-sm text-neutral-600">
                            Consultez vos mesures corporelles ou vos
                            performances sportives.
                        </p>
                    </div>
                </div>

                <HorizontalTabs
                    v-model="activeProgressTab"
                    :tabs="progressTabs"
                    aria-label="Fenêtres d’évolution"
                />
            </section>

            <template v-if="activeProgressTab === 'weight'">
                <section class="rounded-lg bg-evo-white p-4">
                    <div
                        class="flex flex-wrap items-center justify-between gap-3"
                    >
                        <div>
                            <h2 class="text-lg font-semibold">
                                Courbe de poids
                            </h2>
                            <p class="mt-1 text-sm text-neutral-600">
                                Suivi dédié aux mesures corporelles.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="option in rangeOptions"
                                :key="option.value"
                                type="button"
                                class="rounded-xl border px-3 py-1.5 text-sm font-medium transition hover:cursor-pointer"
                                :class="
                                    selectedRange === option.value
                                        ? 'border-neutral-300 bg-evo-orange text-evo-white'
                                        : 'border-neutral-300 bg-evo-purple text-evo-white'
                                "
                                @click="selectedRange = option.value"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 h-80">
                        <WeightChart
                            v-if="filteredWeightEntries.length > 0"
                            :key="chartKey"
                            :weight-entries="filteredWeightEntries"
                        />

                        <div
                            v-else
                            class="flex h-full items-center justify-center rounded-lg border border-dashed border-neutral-300 text-sm text-neutral-500"
                        >
                            Aucune entrée disponible sur cette période.
                        </div>
                    </div>
                </section>

                <section class="rounded-lg bg-evo-white p-4">
                    <h2 class="text-lg font-semibold">
                        Ajouter une entrée de poids
                    </h2>

                    <p class="mt-2 text-sm text-neutral-600">
                        La masse grasse est optionnelle.
                    </p>

                    <div class="mt-4 grid gap-4 md:grid-cols-3">
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
                                class="evo-input"
                            />
                            <p
                                v-if="weightEntryForm.errors.weight"
                                class="text-sm text-red-600"
                            >
                                {{ weightEntryForm.errors.weight }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="entry_date" class="block font-medium">
                                Date de mesure
                            </label>
                            <input
                                id="entry_date"
                                v-model="weightEntryForm.entry_date"
                                type="date"
                                :max="todayDate"
                                class="evo-input"
                            />
                            <p
                                v-if="weightEntryForm.errors.entry_date"
                                class="text-sm text-red-600"
                            >
                                {{ weightEntryForm.errors.entry_date }}
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
                                class="evo-input"
                            />
                            <p
                                v-if="weightEntryForm.errors.body_fat"
                                class="text-sm text-red-600"
                            >
                                {{ weightEntryForm.errors.body_fat }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <Button
                            type="button"
                            :disabled="weightEntryForm.processing"
                            @click="submitWeightEntry"
                        >
                            {{
                                weightEntryForm.processing
                                    ? 'Enregistrement...'
                                    : "Ajouter l'entrée"
                            }}
                        </Button>
                        <p
                            v-if="
                                weightEntryForm.recentlySuccessful ||
                                flashSuccessMessage
                            "
                            class="text-sm text-emerald-700"
                        >
                            {{ flashSuccessMessage ?? 'Entrée enregistrée.' }}
                        </p>
                    </div>
                </section>
            </template>

            <template v-else>
                <PremiumFeatureGate
                    :locked="!canViewPerformanceCharts"
                    feature-name="Graphique de performance"
                    :current-plan="currentSubscriptionPlanName"
                    description="Passez Premium pour visualiser l'évolution détaillée de vos performances."
                    plain-when-unlocked
                >
                    <section class="rounded-lg bg-evo-white p-4">
                        <div
                            class="flex flex-wrap items-start justify-between gap-3"
                        >
                            <div>
                                <h2 class="text-lg font-semibold">
                                    Graphique de performance
                                </h2>
                                <p class="mt-1 text-sm text-neutral-600">
                                    Les métriques disponibles dépendent de
                                    l'exercice.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
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
                                    class="evo-input"
                                    @change="onSportChange"
                                >
                                    <option value="all">Tous les sports</option>
                                    <option
                                        v-for="sport in selectableSports"
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
                                    class="evo-input"
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
                                    class="evo-input"
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
                                <p class="font-medium">Période</p>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="option in rangeOptions"
                                        :key="option.value"
                                        type="button"
                                        class="rounded-xl border px-3 py-1.5 text-sm font-medium transition hover:cursor-pointer"
                                        :class="
                                            selectedPerformanceRange ===
                                            option.value
                                                ? 'border-neutral-300 bg-evo-orange text-evo-white'
                                                : 'border-neutral-300 bg-evo-purple text-evo-white'
                                        "
                                        @click="
                                            selectedPerformanceRange =
                                                option.value
                                        "
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 h-80">
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

                    <template #locked-preview>
                        <div class="min-h-80 rounded-lg bg-evo-white p-4">
                            <h2 class="text-lg font-semibold">
                                Graphique de performance
                            </h2>
                            <p class="mt-1 text-sm text-neutral-600">
                                Visualisez l'évolution de vos performances par
                                exercice, métrique et période.
                            </p>
                            <div
                                class="mt-4 flex h-56 items-center justify-center rounded-lg border border-dashed border-neutral-300 text-sm text-neutral-500"
                            >
                                Aperçu réservé au Premium
                            </div>
                        </div>
                    </template>
                </PremiumFeatureGate>

                <AdInlineSlot :enabled="ads.enabled" />

                <section class="rounded-lg bg-evo-white p-4">
                    <h2 class="text-lg font-semibold">
                        Dernières performances
                    </h2>

                    <div
                        v-if="recentPerformances.length > 0"
                        class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                    >
                        <div
                            v-for="performance in recentPerformances"
                            :key="performance.id"
                            class="rounded-lg border border-neutral-200 bg-white p-4"
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
                                <p class="text-sm text-evo-orange">
                                    {{ performance.dateLabel }}
                                </p>
                            </div>
                            <div
                                class="mt-3 flex flex-wrap gap-2 text-xs text-neutral-600"
                            >
                                <span
                                    v-for="metric in visibleRecentMetricOptions(
                                        performance,
                                    )"
                                    :key="metric.value"
                                    class="rounded-full bg-neutral-100 px-2 py-1"
                                >
                                    {{ metric.label }}:
                                    {{
                                        formatPerformanceValue(
                                            performance,
                                            metric.value,
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
            </template>
        </div>
    </AppLayout>
</template>
