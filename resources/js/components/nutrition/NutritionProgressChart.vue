<script setup lang="ts">
import Chart from 'chart.js/auto';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    consumedCalories: number;
    remainingCalories: number;
    progressPercent: number;
}>();

const progressChartCanvas = ref<HTMLCanvasElement | null>(null);
let progressChart: Chart<'doughnut', number[], string> | null = null;

const progressChartData = computed(() => [
    props.consumedCalories,
    props.remainingCalories,
]);

const updateProgressChart = () => {
    if (!progressChart) {
        return;
    }

    progressChart.data.datasets[0].data = progressChartData.value;
    progressChart.update();
};

onMounted(() => {
    if (!progressChartCanvas.value) {
        return;
    }

    progressChart = new Chart(progressChartCanvas.value, {
        type: 'doughnut',
        data: {
            labels: ['Consommées', 'Restantes'],
            datasets: [
                {
                    data: progressChartData.value,
                    backgroundColor: ['#FF813E', '#F6BE9D'],
                    borderWidth: 0,
                    hoverOffset: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: 0,
            rotation: -90,
            circumference: 180,
            animation: {
                animateRotate: true,
                animateScale: false,
            },
            events: [],
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    enabled: false,
                },
            },
        },
    });
});

watch(progressChartData, updateProgressChart);

onBeforeUnmount(() => {
    progressChart?.destroy();
    progressChart = null;
});
</script>

<template>
    <section class="overflow-hidden rounded-lg bg-evo-white p-4">
        <div class="grid h-full gap-4 md:items-start">
            <div>
                <p
                    class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                >
                    Progression du jour
                </p>
            </div>

            <div class="relative mx-auto h-36 w-72 max-w-full">
                <canvas
                    ref="progressChartCanvas"
                    class="h-full w-full"
                    aria-label="Progression calorique du jour"
                />
                <p
                    class="absolute inset-x-0 top-16 text-center text-3xl font-semibold text-evo-white"
                >
                    {{ progressPercent }}%
                </p>
            </div>
        </div>
    </section>
</template>
