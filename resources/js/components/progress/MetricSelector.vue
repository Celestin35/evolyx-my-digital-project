<script setup lang="ts">
import { computed } from 'vue';
import { rangeOptions } from '@/composables/useProgressRanges';
import type {
    MetricOption,
    PerformanceMetric,
    ProgressRange,
    SelectOption,
} from '@/types/progress';

const props = defineProps<{
    selectedSportId: number | string | 'all';
    selectedExerciseId: number | null;
    selectedMetric: PerformanceMetric;
    selectedPerformanceRange: ProgressRange;
    sports: SelectOption[];
    exercises: SelectOption[];
    metrics: MetricOption[];
}>();

const emit = defineEmits<{
    'update:selectedSportId': [value: number | string | 'all'];
    'update:selectedExerciseId': [value: number | null];
    'update:selectedMetric': [value: PerformanceMetric];
    'update:selectedPerformanceRange': [value: ProgressRange];
    sportChange: [];
    exerciseChange: [];
}>();

const sportModel = computed({
    get: () => props.selectedSportId,
    set: (value) => emit('update:selectedSportId', value),
});

const exerciseModel = computed({
    get: () => props.selectedExerciseId,
    set: (value) => emit('update:selectedExerciseId', value),
});

const metricModel = computed({
    get: () => props.selectedMetric,
    set: (value) => emit('update:selectedMetric', value),
});
</script>

<template>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <label for="performance_sport" class="block font-medium">
                Sport
            </label>
            <select
                id="performance_sport"
                v-model="sportModel"
                class="evo-input"
                @change="emit('sportChange')"
            >
                <option value="all">Tous les sports</option>
                <option
                    v-for="sport in sports"
                    :key="sport.id"
                    :value="sport.id"
                >
                    {{ sport.name }}
                </option>
            </select>
        </div>

        <div class="space-y-2">
            <label for="performance_exercise" class="block font-medium">
                Exercice
            </label>
            <select
                id="performance_exercise"
                v-model="exerciseModel"
                class="evo-input"
                @change="emit('exerciseChange')"
            >
                <option :value="null">Premier exercice disponible</option>
                <option
                    v-for="exercise in exercises"
                    :key="exercise.id"
                    :value="exercise.id"
                >
                    {{ exercise.name }}
                </option>
            </select>
        </div>

        <div class="space-y-2">
            <label for="performance_metric" class="block font-medium">
                Performance
            </label>
            <select
                id="performance_metric"
                v-model="metricModel"
                class="evo-input"
            >
                <option
                    v-for="metric in metrics"
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
                        selectedPerformanceRange === option.value
                            ? 'border-neutral-300 bg-evo-orange text-evo-white'
                            : 'border-neutral-300 bg-evo-purple text-evo-white'
                    "
                    @click="
                        emit('update:selectedPerformanceRange', option.value)
                    "
                >
                    {{ option.label }}
                </button>
            </div>
        </div>
    </div>
</template>
