<script setup lang="ts">
import Chart from 'chart.js/auto';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import 'chartjs-adapter-luxon';
import { transparentize } from '@/lib/utils';

type PerformanceMetric = string;

type PerformanceEntry = {
    id: number;
    performed_at: string | null;
    weight: number | null;
    repetitions: number | null;
    duration_minutes: number | null;
    distance_meters: number | null;
    metric_values: Record<string, number>;
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

const evoPurple = '#7A4896';

const lineShadowPlugin = {
    id: 'performanceLineShadow',
    beforeDatasetDraw(chart: Chart) {
        const { ctx } = chart;

        ctx.save();
        ctx.shadowColor = 'rgba(122, 72, 150, 0.22)';
        ctx.shadowBlur = 8;
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 6;
    },
    afterDatasetDraw(chart: Chart) {
        chart.ctx.restore();
    },
};

const chartTheme = () => {
    const isDark = document.documentElement.classList.contains('dark');

    return {
        gridColor: isDark ? 'rgba(203, 211, 223, 0.18)' : 'rgba(4, 3, 5, 0.12)',
        tickColor: isDark ? '#dfe5ee' : '#525252',
        legendColor: isDark ? '#fbfaf7' : '#525252',
        tooltipBackground: isDark ? '#2b3038' : '#ffffff',
        tooltipText: isDark ? '#fbfaf7' : '#040305',
        tooltipBorder: isDark ? '#596271' : '#e5e5e5',
    };
};

const resolveMetricValue = (performance: PerformanceEntry) => {
    if (props.metric === 'volume') {
        if (performance.weight === null || performance.repetitions === null) {
            return null;
        }

        return performance.weight * performance.repetitions;
    }

    const dynamicValue = performance.metric_values?.[props.metric];

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

    return legacyValues[props.metric] ?? null;
};

onMounted(() => {
    if (!chartCanvas.value) {
        return;
    }

    const theme = chartTheme();

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
                    borderColor: evoPurple,
                    backgroundColor: transparentize(evoPurple, 0.82),
                    pointBackgroundColor: evoPurple,
                    pointBorderColor: evoPurple,
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
            layout: {
                padding: {
                    top: 8,
                },
            },
            plugins: {
                legend: {
                    labels: {
                        color: theme.legendColor,
                    },
                },
                tooltip: {
                    backgroundColor: theme.tooltipBackground,
                    borderColor: theme.tooltipBorder,
                    borderWidth: 1,
                    bodyColor: theme.tooltipText,
                    titleColor: theme.tooltipText,
                    callbacks: {
                        title(items) {
                            const rawPoint = items[0]?.raw as
                                | PerformanceChartPoint
                                | undefined;

                            if (!rawPoint) {
                                return '';
                            }

                            return new Intl.DateTimeFormat('fr-FR', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric',
                            }).format(new Date(rawPoint.dateIso));
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
                        color: theme.tickColor,
                    },
                    grid: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: false,
                    ticks: {
                        color: theme.tickColor,
                        callback(value) {
                            return `${value} ${props.metricUnit}`;
                        },
                    },
                    grid: {
                        color: theme.gridColor,
                    },
                },
            },
        },
        plugins: [lineShadowPlugin],
    }) as Chart<'line', PerformanceChartPoint[], unknown>;
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
