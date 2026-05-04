<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';
import { DateTime } from 'luxon';
import 'chartjs-adapter-luxon';
import { CHART_COLORS, transparentize } from '@/lib/utils';

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

type PerformanceChartPoint = {
    x: Date;
    y: number;
    dateIso: string;
    exerciseName: string;
};

const props = defineProps<{
    performances: PerformanceEntry[];
    metric: PerformanceMetric;
    metricLabel: string;
    metricUnit: string;
}>();

const chartCanvas = ref<HTMLCanvasElement | null>(null);
let performanceChart: Chart<'line', PerformanceChartPoint[], unknown> | null =
    null;

const resolveMetricValue = (performance: PerformanceEntry) => {
    if (props.metric === 'volume') {
        if (performance.weight === null || performance.repetitions === null) {
            return null;
        }

        return performance.weight * performance.repetitions;
    }

    return performance[props.metric];
};

onMounted(() => {
    if (!chartCanvas.value) {
        return;
    }

    const chartData: PerformanceChartPoint[] = props.performances
        .filter((performance) => performance.performed_at !== null)
        .map((performance) => {
            const value = resolveMetricValue(performance);

            if (value === null) {
                return null;
            }

            return {
                x: new Date(performance.performed_at as string),
                y: value,
                dateIso: performance.performed_at as string,
                exerciseName: performance.exercise_name,
            };
        })
        .filter((point): point is PerformanceChartPoint => point !== null)
        .sort(
            (firstPoint, secondPoint) =>
                firstPoint.x.getTime() - secondPoint.x.getTime(),
        );

    performanceChart = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: props.metricLabel,
                    data: chartData,
                    borderColor: CHART_COLORS.purple,
                    backgroundColor: transparentize(CHART_COLORS.purple, 0.82),
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        title(items) {
                            const rawPoint = items[0]?.raw as
                                | PerformanceChartPoint
                                | undefined;

                            if (!rawPoint) {
                                return '';
                            }

                            return DateTime.fromISO(rawPoint.dateIso)
                                .setLocale('fr')
                                .toFormat('d LLLL yyyy');
                        },
                        label(context) {
                            const rawPoint = context.raw as
                                | PerformanceChartPoint
                                | undefined;

                            if (!rawPoint) {
                                return '';
                            }

                            return `${props.metricLabel} : ${rawPoint.y.toFixed(2)} ${props.metricUnit}`;
                        },
                        afterLabel(context) {
                            const rawPoint = context.raw as
                                | PerformanceChartPoint
                                | undefined;

                            return rawPoint?.exerciseName ?? '';
                        },
                    },
                },
            },
            scales: {
                x: {
                    type: 'time',
                    adapters: {
                        date: {
                            locale: 'fr',
                        },
                    },
                    bounds: 'data',
                    time: {
                        unit: 'month',
                        displayFormats: {
                            month: 'LLL',
                            day: 'd LLL',
                        },
                    },
                    ticks: {
                        source: 'auto',
                    },
                },
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback(value) {
                            return `${value} ${props.metricUnit}`;
                        },
                    },
                },
            },
        },
    });
});

onBeforeUnmount(() => {
    performanceChart?.destroy();
    performanceChart = null;
});
</script>

<template>
    <div class="h-full min-h-0 w-full">
        <canvas ref="chartCanvas" class="h-full w-full"></canvas>
    </div>
</template>
