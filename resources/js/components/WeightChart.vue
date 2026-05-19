<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';
import { DateTime } from 'luxon';
import 'chartjs-adapter-luxon';
import { CHART_COLORS, transparentize } from '@/lib/utils';

type WeightEntry = {
    id: number;
    weight: number;
    body_fat: number | null;
    created_at: string | null;
};

const props = defineProps<{
    weightEntries: WeightEntry[];
}>();

type WeightChartPoint = {
    x: Date;
    y: number;
    bodyFat: number | null;
    dateIso: string;
};

const chartCanvas = ref<HTMLCanvasElement | null>(null);
let weightChart: Chart<'line', WeightChartPoint[], unknown> | null = null;

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

onMounted(() => {
    if (!chartCanvas.value) {
        return;
    }

    const theme = chartTheme();

    const chartData: WeightChartPoint[] = props.weightEntries
        .filter((entry) => entry.created_at !== null)
        .map((entry) => ({
            x: new Date(entry.created_at as string),
            y: entry.weight,
            bodyFat: entry.body_fat,
            dateIso: entry.created_at as string,
        }));

    weightChart = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: 'Poids',
                    data: chartData,
                    borderColor: CHART_COLORS.orange,
                    backgroundColor: transparentize(CHART_COLORS.orange, 0.8),
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
                                | WeightChartPoint
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
                                | WeightChartPoint
                                | undefined;

                            if (!rawPoint) {
                                return '';
                            }

                            return `Poids : ${rawPoint.y.toFixed(2)} kg`;
                        },
                        afterLabel(context) {
                            const rawPoint = context.raw as
                                | WeightChartPoint
                                | undefined;

                            if (!rawPoint || rawPoint.bodyFat === null) {
                                return 'Body fat : non renseigne';
                            }

                            return `Body fat : ${rawPoint.bodyFat.toFixed(2)} %`;
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
                        tooltipFormat: 'd LLLL yyyy',
                    },
                    ticks: {
                        source: 'auto',
                        color: theme.tickColor,
                    },
                    grid: {
                        color: theme.gridColor,
                    },
                },
                y: {
                    beginAtZero: false,
                    ticks: {
                        color: theme.tickColor,
                        callback(value) {
                            return `${value} kg`;
                        },
                    },
                    grid: {
                        color: theme.gridColor,
                    },
                },
            },
        },
    });
});

onBeforeUnmount(() => {
    weightChart?.destroy();
    weightChart = null;
});
</script>

<template>
    <div class="min-h-0 h-full w-full">
        <canvas ref="chartCanvas" class="h-full w-full"></canvas>
    </div>
</template>
