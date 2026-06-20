<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdInlineSlot from '@/components/ads/AdInlineSlot.vue';
import CaloriesOverviewCard from '@/components/nutrition/CaloriesOverviewCard.vue';
import MacrosEditor from '@/components/nutrition/MacrosEditor.vue';
import NutritionProgressChart from '@/components/nutrition/NutritionProgressChart.vue';
import { Button } from '@/components/ui/button';
import { useAds } from '@/composables/useAds';
import { useMacrosForm } from '@/composables/useMacrosForm';
import AppLayout from '@/layouts/AppLayout.vue';
import { profile } from '@/routes';
import type { CaloriesOverview } from '@/types/nutrition';

const props = defineProps<{
    caloriesOverview: CaloriesOverview;
    can_edit_macros: boolean;
    active_subscription_plan: string | null;
}>();

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

const canEditMacros = computed(() => props.can_edit_macros);
const showNutritionAd = computed(() => ads.enabled && !canEditMacros.value);
const macroErrorMessage = computed(() => page.props.errors?.macros);
const flashSuccessMessage = computed(() => page.props.flash?.success);

const {
    isEditingMacros,
    macrosForm,
    editedCalories,
    caloriesDelta,
    isCaloriesOverBase,
    startMacrosEdit,
    cancelMacrosEdit,
    stepMacro,
    saveMacros,
} = useMacrosForm(props.caloriesOverview);
</script>

<template>
    <Head title="Nutrition" />

    <AppLayout
        title="Nutrition"
        subtitle="Visualisez directement vos calories cibles à partir de votre objectif."
    >
        <div
            v-if="caloriesOverview.target_calories"
            class="space-y-4 max-lg:pb-23"
        >
            <div class="grid gap-4 xl:grid-cols-2">
                <CaloriesOverviewCard
                    :target-calories="caloriesOverview.target_calories"
                    :consumed-calories="consumedCalories"
                    :remaining-calories="remainingCalories"
                />

                <NutritionProgressChart
                    :consumed-calories="consumedCalories"
                    :remaining-calories="remainingCalories"
                    :progress-percent="progressPercent"
                />
            </div>

            <div
                class="grid gap-4"
                :class="showNutritionAd ? 'lg:grid-cols-2' : ''"
            >
                <MacrosEditor
                    :calories-overview="caloriesOverview"
                    :can-edit-macros="canEditMacros"
                    :active-subscription-plan="active_subscription_plan"
                    :is-editing-macros="isEditingMacros"
                    :macros-form="macrosForm"
                    :edited-calories="editedCalories"
                    :calories-delta="caloriesDelta"
                    :is-calories-over-base="isCaloriesOverBase"
                    :macro-error-message="macroErrorMessage"
                    :flash-success-message="flashSuccessMessage"
                    @start-edit="startMacrosEdit"
                    @cancel-edit="cancelMacrosEdit"
                    @save="saveMacros"
                    @step-macro="stepMacro"
                />

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
                        Cette colonne montre simplement à quoi pourrait
                        ressembler l'ajout manuel de calories plus tard, sans
                        logique métier pour le moment.
                    </p>
                </div>

                <div class="mt-10 grid gap-5 xl:grid-cols-2">
                    <div
                        class="rounded-2xl border border-neutral-400 bg-evo-gray p-5 shadow-sm"
                    >
                        <h3 class="text-2xl font-semibold text-evo-black">
                            Ajouter un repas
                        </h3>

                        <div class="mt-6 space-y-3">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold">
                                    Moment du repas
                                </label>
                                <div
                                    class="flex flex-wrap gap-2 text-sm text-evo-black"
                                >
                                    <span
                                        class="rounded-full border border-evo-purple px-3 py-1 leading-none"
                                    >
                                        Petit-dejeuner
                                    </span>
                                    <span
                                        class="rounded-full border border-evo-purple px-3 py-1 leading-none"
                                    >
                                        Dejeuner
                                    </span>
                                    <span
                                        class="rounded-full bg-evo-purple px-3 py-1 leading-none text-evo-white"
                                    >
                                        Diner
                                    </span>
                                    <span
                                        class="rounded-full border border-evo-purple px-3 py-1 leading-none"
                                    >
                                        Collation
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold">
                                    Description
                                </label>
                                <div
                                    class="rounded-full border border-neutral-400 bg-white px-5 py-3 text-sm text-neutral-500 shadow-sm"
                                >
                                    500 grammes de riz et 2 cuisses de poulets
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold">
                                        Calories estimees
                                    </label>
                                    <div
                                        class="rounded-full border border-neutral-400 bg-white px-5 py-3 text-sm text-evo-orange shadow-sm"
                                    >
                                        800 calories
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold">
                                        Heure
                                    </label>
                                    <div
                                        class="rounded-full border border-neutral-400 bg-white px-5 py-3 text-sm text-evo-orange shadow-sm"
                                    >
                                        20:15
                                    </div>
                                </div>
                            </div>

                            <Button type="button" disabled>
                                Ajouter ce repas
                            </Button>

                            <div class="pt-8">
                                <h3
                                    class="text-2xl font-semibold text-evo-black"
                                >
                                    Reste apres ajout
                                </h3>
                                <div
                                    class="mt-2 flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-7 shadow-sm"
                                >
                                    <p
                                        class="text-2xl font-semibold text-evo-orange"
                                    >
                                        {{
                                            Math.max(0, remainingCalories - 800)
                                        }}
                                        calories
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        -800 calories
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-neutral-400 bg-evo-gray p-5 shadow-sm"
                    >
                        <h3 class="text-2xl font-semibold text-evo-black">
                            Journal du jour
                        </h3>

                        <div class="mt-6 space-y-3">
                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-6 shadow-sm"
                            >
                                <div>
                                    <p
                                        class="text-lg font-semibold text-evo-black"
                                    >
                                        Petit-dejeuner | 8h20
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        Porridge, banane, beurre de cacahuetes
                                    </p>
                                </div>
                                <p
                                    class="text-2xl font-semibold text-evo-orange"
                                >
                                    450Kcal
                                </p>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-6 shadow-sm"
                            >
                                <div>
                                    <p
                                        class="text-lg font-semibold text-evo-black"
                                    >
                                        Dejeuner | 13h15
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        Pates, legumes, steak hache
                                    </p>
                                </div>
                                <p
                                    class="text-2xl font-semibold text-evo-orange"
                                >
                                    725Kcal
                                </p>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-6 shadow-sm"
                            >
                                <div>
                                    <p
                                        class="text-lg font-semibold text-evo-black"
                                    >
                                        Collation | 16h
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        Barre de cereales
                                    </p>
                                </div>
                                <p
                                    class="text-2xl font-semibold text-evo-orange"
                                >
                                    100Kcal
                                </p>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-evo-orange bg-white px-5 py-6 shadow-sm"
                            >
                                <div>
                                    <p
                                        class="text-lg font-semibold text-evo-black"
                                    >
                                        Diner | 20h15
                                    </p>
                                    <p class="text-sm text-evo-black">
                                        500 g de riz et 2 morceaux de poulet
                                    </p>
                                </div>
                                <p
                                    class="text-2xl font-semibold text-evo-orange"
                                >
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
