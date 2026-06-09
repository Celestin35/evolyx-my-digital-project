<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';
import AdInlineSlot from '@/components/ads/AdInlineSlot.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureGate from '@/components/PremiumFeatureGate.vue';
import { Button } from '@/components/ui/button';
import { useAds } from '@/composables/useAds';
import { profile } from '@/routes';

type CaloriesOverview = {
    current_weight: string | number | null;
    target_calories: number | null;
    consumed_calories: number | null;
    macros: {
        protein: number;
        fats: number;
        carbs: number;
    } | null;
};

const props = defineProps<{
    caloriesOverview: CaloriesOverview;
    can_edit_macros: boolean;
    active_subscription_plan: string | null;
}>();

type MacrosField = 'protein' | 'carbs' | 'fats';
type NutritionPageProps = {
    flash?: {
        success?: string;
    };
    errors?: {
        macros?: string;
    };
};

const page = usePage<NutritionPageProps>();
const ads = useAds();
const targetCalories = computed(
    () => props.caloriesOverview.target_calories ?? 0,
);
const consumedCalories = computed(
    () => props.caloriesOverview.consumed_calories ?? 0,
);
const remainingCalories = computed(() =>
    Math.max(0, targetCalories.value - consumedCalories.value),
);
const progressPercent = computed(() => {
    if (targetCalories.value <= 0) {
        return 0;
    }

    return Math.min(
        100,
        Math.round((consumedCalories.value / targetCalories.value) * 100),
    );
});

const isEditingMacros = ref(false);
const progressChartCanvas = ref<HTMLCanvasElement | null>(null);
let progressChart: Chart<'doughnut', number[], string> | null = null;

const macrosForm = useForm({
    protein: props.caloriesOverview.macros?.protein ?? 0,
    carbs: props.caloriesOverview.macros?.carbs ?? 0,
    fats: props.caloriesOverview.macros?.fats ?? 0,
});

const canEditMacros = computed(() => props.can_edit_macros);
const showNutritionAd = computed(() => ads.enabled && !canEditMacros.value);
const baseTargetCalories = computed(
    () => props.caloriesOverview.target_calories ?? 0,
);
const editedCalories = computed(() => {
    const protein = Number(macrosForm.protein) || 0;
    const carbs = Number(macrosForm.carbs) || 0;
    const fats = Number(macrosForm.fats) || 0;

    return protein * 4 + carbs * 4 + fats * 9;
});
const caloriesDelta = computed(
    () => editedCalories.value - baseTargetCalories.value,
);
const isCaloriesOverBase = computed(
    () => editedCalories.value > baseTargetCalories.value,
);
const macroErrorMessage = computed(() => page.props.errors?.macros);
const flashSuccessMessage = computed(() => page.props.flash?.success);

const startMacrosEdit = () => {
    if (!canEditMacros.value) {
        return;
    }

    macrosForm.defaults({
        protein: props.caloriesOverview.macros?.protein ?? 0,
        carbs: props.caloriesOverview.macros?.carbs ?? 0,
        fats: props.caloriesOverview.macros?.fats ?? 0,
    });
    macrosForm.reset();
    macrosForm.clearErrors();
    isEditingMacros.value = true;
};

const cancelMacrosEdit = () => {
    macrosForm.reset();
    macrosForm.clearErrors();
    isEditingMacros.value = false;
};

const stepMacro = (field: MacrosField, delta: number) => {
    const currentValue = Number(macrosForm[field]) || 0;
    const nextValue = Math.max(0, currentValue + delta);
    macrosForm[field] = nextValue;
};

const saveMacros = () => {
    macrosForm.patch('/nutrition/macros', {
        preserveScroll: true,
        onSuccess: () => {
            isEditingMacros.value = false;
        },
    });
};

const progressChartData = computed(() => [
    consumedCalories.value,
    remainingCalories.value,
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
            labels: ['Consommees', 'Restantes'],
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
    <Head title="Nutrition" />

    <AppLayout
        title="Nutrition"
        subtitle="Visualisez directement vos calories cibles à partir de votre objectif."
    >
        <div v-if="caloriesOverview.target_calories" class="space-y-4">
            <div class="grid gap-4 xl:grid-cols-2">
                <section class="rounded-lg bg-evo-white p-4">
                    <div class="flex h-full flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="h-full flex flex-col justify-between">
                            <p class="text-sm font-medium tracking-wide text-neutral-500 uppercase ">
                                Objectif du jour
                            </p>
                            <div>
                                <p class="mt-5 text-6xl font-semibold leading-none text-evo-black">
                                {{ caloriesOverview.target_calories }}
                            </p>
                            <p class="mt-2 text-base text-neutral-600">
                                kcal à consommer aujourd'hui
                            </p>
                            </div>
                            
                        </div>

                        <div class="max-w-md space-y-4 sm:text-right h-full flex flex-col justify-between">
                            <div class="w-fit sm:self-end">
                                <Button :as="Link" :href="profile()">
                                Modifier mon objectif
                            </Button>
                            </div>
                            
                            <div class="mt-5 rounded-lg border border-neutral-300 bg-evo-gray px-4 py-3 shadow-sm">
                                <p class="text-base text-evo-black">
                                    {{ consumedCalories }} kcal consommées
                                </p>
                                <p class="text-base text-evo-black">
                                    {{ remainingCalories }} kcal restantes
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-lg bg-evo-white p-4">
                    <div class="grid h-full gap-4 md:items-start">
                        <div>
                            <p class="text-sm font-medium tracking-wide text-neutral-500 uppercase">
                                Progression du jour
                            </p>

                            
                        </div>

                        <div class="relative mx-auto h-36 w-72 max-w-full">
                            <canvas
                                ref="progressChartCanvas"
                                class="h-full w-full"
                                aria-label="Progression calorique du jour"
                            />
                            <p class="absolute inset-x-0 top-16 text-center text-3xl font-semibold text-evo-white">
                                {{ progressPercent }}%
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <div
                class="grid gap-4"
                :class="showNutritionAd ? 'lg:grid-cols-2' : ''"
            >
                <PremiumFeatureGate
                    :locked="!canEditMacros"
                    feature-name="Gestion des macros nutriment"
                    :current-plan="active_subscription_plan"
                    class="h-full"
                >
                    <div class="transition">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-medium tracking-wide text-neutral-500 uppercase">
                                Gestion des macros nutriment
                            </p>
                            <Button
                                v-if="canEditMacros && !isEditingMacros"
                                type="button"
                                @click="startMacrosEdit"
                            >
                                Modifier mes macros
                            </Button>
                        </div>

                        <div v-if="!isEditingMacros" class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                <p class="text-sm text-neutral-500">Proteines</p>
                                <p class="mt-2 text-2xl font-semibold">
                                    {{ caloriesOverview.macros?.protein ?? '-' }} g
                                </p>
                            </div>
                            <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                <p class="text-sm text-neutral-500">Glucides</p>
                                <p class="mt-2 text-2xl font-semibold">
                                    {{ caloriesOverview.macros?.carbs ?? '-' }} g
                                </p>
                            </div>
                            <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                <p class="text-sm text-neutral-500">Lipides</p>
                                <p class="mt-2 text-2xl font-semibold">
                                    {{ caloriesOverview.macros?.fats ?? '-' }} g
                                </p>
                            </div>
                        </div>

                        <div v-else class="mt-4 space-y-4">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                    <p class="text-sm text-neutral-500">Proteines</p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <button type="button" class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100" @click="stepMacro('protein', -1)">-</button>
                                        <p class="text-2xl font-semibold">{{ macrosForm.protein }} g</p>
                                        <button type="button" class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100" @click="stepMacro('protein', 1)">+</button>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                    <p class="text-sm text-neutral-500">Glucides</p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <button type="button" class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100" @click="stepMacro('carbs', -1)">-</button>
                                        <p class="text-2xl font-semibold">{{ macrosForm.carbs }} g</p>
                                        <button type="button" class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100" @click="stepMacro('carbs', 1)">+</button>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                    <p class="text-sm text-neutral-500">Lipides</p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <button type="button" class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100" @click="stepMacro('fats', -1)">-</button>
                                        <p class="text-2xl font-semibold">{{ macrosForm.fats }} g</p>
                                        <button type="button" class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100" @click="stepMacro('fats', 1)">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                                <p class="text-sm text-neutral-600">Calories estimees avec ces macros</p>
                                <p class="mt-2 text-3xl font-semibold" :class="isCaloriesOverBase ? 'text-red-600' : 'text-emerald-600'">
                                    {{ editedCalories }} kcal
                                </p>
                                <p class="mt-1 text-sm text-neutral-600">
                                    Delta vs objectif actuel: {{ caloriesDelta > 0 ? '+' : '' }}{{ caloriesDelta }} kcal
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <Button type="button" :disabled="macrosForm.processing" @click="saveMacros">
                                    {{ macrosForm.processing ? 'Enregistrement...' : 'Enregistrer' }}
                                </Button>
                                <Button type="button" variant="transparent" @click="cancelMacrosEdit">
                                    Annuler
                                </Button>
                            </div>
                        </div>

                        <p v-if="macroErrorMessage" class="mt-4 text-sm text-red-600">
                            {{ macroErrorMessage }}
                        </p>
                        <p v-if="flashSuccessMessage" class="mt-4 text-sm text-emerald-700">
                            {{ flashSuccessMessage }}
                        </p>
                    </div>

                    <template #locked-preview>
                        <div class="pointer-events-none opacity-35 blur-[5px] select-none">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-medium tracking-wide text-neutral-500 uppercase">
                                    Gestion des macros nutriment
                                </p>
                                <div class="h-9 w-32 rounded-full border border-neutral-300" />
                            </div>

                            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                    <p class="text-sm text-neutral-500">Proteines</p>
                                    <p class="mt-2 text-2xl font-semibold">-- g</p>
                                </div>
                                <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                    <p class="text-sm text-neutral-500">Glucides</p>
                                    <p class="mt-2 text-2xl font-semibold">-- g</p>
                                </div>
                                <div class="rounded-lg border border-neutral-200 bg-white p-4">
                                    <p class="text-sm text-neutral-500">Lipides</p>
                                    <p class="mt-2 text-2xl font-semibold">-- g</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </PremiumFeatureGate>

                <AdInlineSlot
                    v-if="showNutritionAd"
                    :enabled="true"
                    variant="square"
                    class="h-full"
                />
            </div>
            <section class="rounded-lg bg-evo-white p-4 sm:p-6">
                <div>
                    <h2 class="text-2xl font-semibold text-evo-black">
                        Saisie des repas
                    </h2>
                    <p class="mt-1 max-w-xl text-sm leading-4 text-neutral-500">
                        Cette colonne montre simplement à quoi pourrait ressembler l'ajout manuel de
                        calories plus tard, sans logique métier pour le moment.
                    </p>
                </div>

                <div class="mt-10 grid gap-5 xl:grid-cols-2">
                    <div class="rounded-2xl border border-neutral-400 bg-evo-gray p-5 shadow-sm">
                        <h3 class="text-2xl font-semibold text-evo-black">
                            Ajouter un repas
                        </h3>

                        <div class="mt-6 space-y-3">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold">
                                    Moment du repas
                                </label>
                                <div class="flex flex-wrap gap-2 text-sm text-evo-black">
                                    <span class="rounded-full border border-evo-purple px-3 py-1 leading-none">
                                        Petit-dejeuner
                                    </span>
                                    <span class="rounded-full border border-evo-purple px-3 py-1 leading-none">
                                        Dejeuner
                                    </span>
                                    <span class="rounded-full bg-evo-purple px-3 py-1 leading-none text-evo-white">
                                        Diner
                                    </span>
                                    <span class="rounded-full border border-evo-purple px-3 py-1 leading-none">
                                        Collation
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold">
                                    Description
                                </label>
                                <div class="rounded-full border border-neutral-400 bg-white px-5 py-3 text-sm text-neutral-500 shadow-sm">
                                    500 grammes de riz et 2 cuisses de poulets
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold">
                                        Calories estimees
                                    </label>
                                    <div class="rounded-full border border-neutral-400 bg-white px-5 py-3 text-sm text-evo-orange shadow-sm">
                                        800 calories
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold">
                                        Heure
                                    </label>
                                    <div class="rounded-full border border-neutral-400 bg-white px-5 py-3 text-sm text-evo-orange shadow-sm">
                                        20:15
                                    </div>
                                </div>
                            </div>

                            <Button type="button" disabled>
                                Ajouter ce repas
                            </Button>

                            <div class="pt-8">
                                <h3 class="text-2xl font-semibold text-evo-black">
                                    Reste apres ajout
                                </h3>
                                <div class="mt-2 flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-7 shadow-sm">
                                    <p class="text-2xl font-semibold text-evo-orange">
                                        {{ Math.max(0, remainingCalories - 800) }} calories
                                    </p>
                                    <p class="text-sm text-evo-black">-800 calories</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-neutral-400 bg-evo-gray p-5 shadow-sm">
                        <h3 class="text-2xl font-semibold text-evo-black">
                            Journal du jour
                        </h3>

                        <div class="mt-6 space-y-3">
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-6 shadow-sm">
                                <div>
                                    <p class="text-lg font-semibold text-evo-black">
                                        Petit-dejeuner | 8h20
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        Porridge, banane, beurre de cacahuetes
                                    </p>
                                </div>
                                <p class="text-2xl font-semibold text-evo-orange">
                                    450Kcal
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-6 shadow-sm">
                                <div>
                                    <p class="text-lg font-semibold text-evo-black">
                                        Dejeuner | 13h15
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        Pates, legumes, steak hache
                                    </p>
                                </div>
                                <p class="text-2xl font-semibold text-evo-orange">
                                    725Kcal
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-6 shadow-sm">
                                <div>
                                    <p class="text-lg font-semibold text-evo-black">
                                        Collation | 16h
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        Barre de cereales
                                    </p>
                                </div>
                                <p class="text-2xl font-semibold text-evo-orange">
                                    100Kcal
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-6 shadow-sm">
                                <div>
                                    <p class="text-lg font-semibold text-evo-black">
                                        Diner | 20h15
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        500 g de riz et 2 morceaux de poulet
                                    </p>
                                </div>
                                <p class="text-2xl font-semibold text-evo-orange">
                                    800Kcal
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section v-else class="rounded-lg bg-evo-white p-4">
            <h2 class="text-lg font-semibold">Nutrition</h2>
            <p class="mt-3 text-sm text-neutral-600">
                Aucun objectif actif n'est disponible pour calculer vos calories
                du jour.
            </p>
            <Button :as="Link" :href="profile()" class="mt-4">
                Créer ou modifier mon objectif
            </Button>
        </section>
    </AppLayout>
</template>
