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

onMounted(() => {
    if (!chartCanvas.value) {
        return;
    }

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
                tooltip: {
                    callbacks: {
                        title(items) {
                            const rawPoint = items[0]?.raw as
                                | WeightChartPoint
                                | undefined;

                            if (!rawPoint) {
                                return '';
                            }

                            return DateTime.fromISO(rawPoint.dateIso).toFormat(
                                'd LLLL yyyy',
                            );
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
                    },
                },
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback(value) {
                            return `${value} kg`;
                        },
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
