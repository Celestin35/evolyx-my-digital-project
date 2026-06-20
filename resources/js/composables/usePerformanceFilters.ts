import { computed, ref, toValue, watch, type MaybeRefOrGetter } from 'vue';
import { getRangeCutoffDate } from '@/composables/useProgressRanges';
import type {
    MetricOption,
    PerformanceEntry,
    PerformanceMetric,
    ProgressRange,
    SelectOption,
    SportOption,
} from '@/types/progress';

const legacyMetricOptions: MetricOption[] = [
    { value: 'weight', label: 'Charge', unit: 'kg' },
    { value: 'repetitions', label: 'Répétitions', unit: 'rep' },
    { value: 'volume', label: 'Volume', unit: 'kg' },
    { value: 'duration_minutes', label: 'Durée', unit: 'min' },
    { value: 'distance_meters', label: 'Distance', unit: 'm' },
];

const normalizeId = (id: number | string) => Number(id);

export function usePerformanceFilters(
    performances: MaybeRefOrGetter<PerformanceEntry[]>,
    sports: MaybeRefOrGetter<SportOption[]>,
) {
    const selectedPerformanceRange = ref<ProgressRange>('3m');
    const selectedSportId = ref<number | string | 'all'>('all');
    const selectedExerciseId = ref<number | null>(null);
    const selectedMetric = ref<PerformanceMetric>('');

    const selectableSports = computed<SelectOption[]>(() => {
        const availableSports = new Map<number, string>(
            toValue(sports).map((sport) => [normalizeId(sport.id), sport.name]),
        );

        toValue(performances).forEach((performance) => {
            if (performance.sport_id === null || !performance.sport_name) {
                return;
            }

            availableSports.set(
                normalizeId(performance.sport_id),
                performance.sport_name,
            );
        });

        return [...availableSports.entries()]
            .map(([id, name]) => ({ id, name }))
            .sort((firstSport, secondSport) =>
                firstSport.name.localeCompare(secondSport.name),
            );
    });

    const performancesForSelectedSport = computed(() => {
        if (selectedSportId.value === 'all') {
            return toValue(performances);
        }

        return toValue(performances).filter(
            (performance) =>
                performance.sport_id !== null &&
                normalizeId(performance.sport_id) ===
                    normalizeId(selectedSportId.value),
        );
    });

    const exercisesWithPerformances = computed<SelectOption[]>(() => {
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
        const fallbackExerciseId =
            exercisesWithPerformances.value[0]?.id ?? null;
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
                performance.metric_values?.repetitions ??
                performance.repetitions;

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
                (performance) =>
                    resolveMetricValue(performance, 'volume') !== null,
            )
        ) {
            options.set('volume', {
                value: 'volume',
                label: 'Volume',
                unit: 'kg',
            });
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
                resolveMetricValue(performance, activeMetric.value.value) !==
                null
            );
        }),
    );

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
        [...toValue(performances)]
            .filter((performance) => performance.performed_at)
            .sort(
                (firstPerformance, secondPerformance) =>
                    new Date(
                        secondPerformance.performed_at as string,
                    ).getTime() -
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
                (metric) =>
                    resolveMetricValue(performance, metric.key) !== null,
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

    return {
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
    };
}
