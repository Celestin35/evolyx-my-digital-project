<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdInlineSlot from '@/components/ads/AdInlineSlot.vue';
import HorizontalTabs from '@/components/HorizontalTabs.vue';
import PerformanceChart from '@/components/PerformanceChart.vue';
import PremiumFeatureGate from '@/components/PremiumFeatureGate.vue';
import MetricSelector from '@/components/progress/MetricSelector.vue';
import WeightEntryForm from '@/components/progress/WeightEntryForm.vue';
import WeightChart from '@/components/WeightChart.vue';
import { useAds } from '@/composables/useAds';
import { usePerformanceFilters } from '@/composables/usePerformanceFilters';
import {
    formatDateInput,
    rangeOptions,
    useProgressRanges,
} from '@/composables/useProgressRanges';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    PerformanceEntry,
    SportOption,
    WeightEntry,
} from '@/types/progress';

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

const { selectedRange, filteredWeightEntries, chartKey, todayDate } =
    useProgressRanges(() => props.weightEntries);

const {
    selectedPerformanceRange,
    selectedSportId,
    selectedExerciseId,
    selectedMetric,
    selectableSports,
    exercisesWithPerformances,
    availableMetricOptions,
    activeMetric,
    chartPerformanceEntries,
    performanceChartKey,
    recentPerformances,
    formatPerformanceValue,
    visibleRecentMetricOptions,
    onSportChange,
    onExerciseChange,
} = usePerformanceFilters(
    () => props.performances,
    () => props.sports,
);

const weightEntryForm = useForm({
    weight: '',
    body_fat: '',
    entry_date: formatDateInput(new Date()),
});

const flashSuccessMessage = computed(() => page.props.flash?.success);

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

                <WeightEntryForm
                    :form="weightEntryForm"
                    :today-date="todayDate"
                    :flash-success-message="flashSuccessMessage"
                    @update-weight="weightEntryForm.weight = $event"
                    @update-body-fat="weightEntryForm.body_fat = $event"
                    @update-entry-date="weightEntryForm.entry_date = $event"
                    @submit="submitWeightEntry"
                />
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

                        <MetricSelector
                            v-model:selected-sport-id="selectedSportId"
                            v-model:selected-exercise-id="selectedExerciseId"
                            v-model:selected-metric="selectedMetric"
                            v-model:selected-performance-range="
                                selectedPerformanceRange
                            "
                            :sports="selectableSports"
                            :exercises="exercisesWithPerformances"
                            :metrics="availableMetricOptions"
                            @sport-change="onSportChange"
                            @exercise-change="onExerciseChange"
                        />
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
