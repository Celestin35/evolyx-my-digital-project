<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { profile } from '@/routes';
import { formatFrenchDate } from '@/lib/profile';

type CaloriesOverview = {
    current_weight: string | number | null;
    target_calories: number | null;
    maintenance_calories: number | null;
    daily_adjustment: number | null;
    goal_type: string | null;
    goal_end_date: string | null;
    target_weight: string | number | null;
    consumed_calories: number | null;
    macros: {
        protein: number;
        fats: number;
        carbs: number;
    } | null;
};

const props = defineProps<{
    caloriesOverview: CaloriesOverview;
}>();

const targetCalories = computed(() => props.caloriesOverview.target_calories ?? 0);
const consumedCalories = computed(() => props.caloriesOverview.consumed_calories ?? 0);
const remainingCalories = computed(() =>
    Math.max(0, targetCalories.value - consumedCalories.value),
);
const progressPercent = computed(() => {
    if (targetCalories.value <= 0) {
        return 0;
    }

    return Math.min(100, Math.round((consumedCalories.value / targetCalories.value) * 100));
});

const ringStyle = computed(() => {
    return {
        background: `conic-gradient(var(--color-evo-purple) ${progressPercent.value}%, #ececec 0%)`,
    };
});

const adjustmentLabel = computed(() => {
    const adjustment = props.caloriesOverview.daily_adjustment;

    if (adjustment === null || adjustment === 0) {
        return 'Aucun ajustement';
    }

    if ((props.caloriesOverview.goal_type ?? '').toLowerCase().includes('perte')) {
        return `Deficit de ${adjustment} kcal / jour`;
    }

    if ((props.caloriesOverview.goal_type ?? '').toLowerCase().includes('prise')) {
        return `Surplus de ${adjustment} kcal / jour`;
    }

    return `${adjustment} kcal / jour`;
});

const formattedGoalEndDate = computed(() =>
    formatFrenchDate(props.caloriesOverview.goal_end_date),
);
</script>

<template>
    <Head title="Nutrition" />

    <AppLayout
        title="Nutrition"
        subtitle="Visualisez directement vos calories cibles a partir de votre objectif."
    >
        <div v-if="caloriesOverview.target_calories" class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-lg bg-white p-6">
                <p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
                    Objectif du jour
                </p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div class="space-y-2">
                        <p class="text-5xl font-semibold text-evo-black">
                            {{ caloriesOverview.target_calories }}
                        </p>
                        <p class="text-sm text-neutral-600">kcal a consommer aujourd'hui</p>
                    </div>
                    <Link
                        :href="profile()"
                        class="inline-flex rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:bg-neutral-100"
                    >
                        Modifier mon objectif
                    </Link>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-neutral-200 p-4">
                        <p class="text-sm text-neutral-500">Maintenance</p>
                        <p class="mt-2 text-2xl font-semibold">
                            {{ caloriesOverview.maintenance_calories ?? '-' }} kcal
                        </p>
                    </div>
                    <div class="rounded-lg border border-neutral-200 p-4">
                        <p class="text-sm text-neutral-500">Ajustement</p>
                        <p class="mt-2 text-2xl font-semibold">
                            {{ adjustmentLabel }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-lg bg-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
                            Progression du jour
                        </p>
                        <p class="mt-2 text-sm text-neutral-600">
                            Visualisation de demo en attendant le suivi repas.
                        </p>
                    </div>
                    <p class="text-sm font-medium text-evo-purple">{{ progressPercent }}%</p>
                </div>

                <div class="mt-6 flex flex-col items-center gap-6 md:flex-row md:justify-between">
                    <div class="relative flex h-44 w-44 items-center justify-center rounded-full" :style="ringStyle">
                        <div class="flex h-32 w-32 flex-col items-center justify-center rounded-full bg-white text-center">
                            <p class="text-3xl font-semibold">{{ remainingCalories }}</p>
                            <p class="text-xs text-neutral-500">kcal restantes</p>
                        </div>
                    </div>

                    <div class="w-full max-w-xs space-y-3">
                        <div class="rounded-lg border border-neutral-200 p-4">
                            <p class="text-sm text-neutral-500">Consommees</p>
                            <p class="mt-2 text-2xl font-semibold">{{ consumedCalories }} kcal</p>
                        </div>
                        <div class="rounded-lg border border-neutral-200 p-4">
                            <p class="text-sm text-neutral-500">Objectif</p>
                            <p class="mt-2 text-2xl font-semibold">{{ targetCalories }} kcal</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg bg-white p-6">
                <p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
                    Repartition cible des macros
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-neutral-200 p-4">
                        <p class="text-sm text-neutral-500">Proteines</p>
                        <p class="mt-2 text-2xl font-semibold">
                            {{ caloriesOverview.macros?.protein ?? '-' }} g
                        </p>
                    </div>
                    <div class="rounded-lg border border-neutral-200 p-4">
                        <p class="text-sm text-neutral-500">Glucides</p>
                        <p class="mt-2 text-2xl font-semibold">
                            {{ caloriesOverview.macros?.carbs ?? '-' }} g
                        </p>
                    </div>
                    <div class="rounded-lg border border-neutral-200 p-4">
                        <p class="text-sm text-neutral-500">Lipides</p>
                        <p class="mt-2 text-2xl font-semibold">
                            {{ caloriesOverview.macros?.fats ?? '-' }} g
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-sm text-neutral-600">
                    Ces valeurs viennent directement de votre objectif actif.
                </p>
            </section>

            <section class="rounded-lg bg-white p-6">
                <p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
                    Resume objectif
                </p>

                <div class="mt-6 space-y-3 text-sm text-neutral-700">
                    <div class="flex items-center justify-between rounded-lg border border-neutral-200 p-4">
                        <span>Type d'objectif</span>
                        <span class="font-semibold text-evo-black">
                            {{ caloriesOverview.goal_type ?? 'Non defini' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-neutral-200 p-4">
                        <span>Poids cible</span>
                        <span class="font-semibold text-evo-black">
                            {{ caloriesOverview.target_weight ?? 'Non defini' }} kg
                        </span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-neutral-200 p-4">
                        <span>Date estimee</span>
                        <span class="font-semibold text-evo-black">
                            {{ formattedGoalEndDate ?? 'Non definie' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-neutral-200 p-4">
                        <span>Poids actuel</span>
                        <span class="font-semibold text-evo-black">
                            {{ caloriesOverview.current_weight ?? 'Non defini' }} kg
                        </span>
                    </div>
                </div>
            </section>
        </div>

        <section v-else class="rounded-lg bg-white p-6">
            <h2 class="text-lg font-semibold">Nutrition</h2>
            <p class="mt-3 text-sm text-neutral-600">
                Aucun objectif actif n'est disponible pour calculer vos calories du jour.
            </p>
            <Link
                :href="profile()"
                class="mt-5 inline-flex rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:opacity-90"
            >
                Creer ou modifier mon objectif
            </Link>
        </section>
    </AppLayout>
</template>
