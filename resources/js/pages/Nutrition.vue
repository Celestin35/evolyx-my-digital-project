<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdInlineSlot from '@/components/ads/AdInlineSlot.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureGate from '@/components/PremiumFeatureGate.vue';
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

const ringStyle = computed(() => {
    return {
        background: `conic-gradient(var(--color-evo-purple) ${progressPercent.value}%, #ececec 0%)`,
    };
});

const isEditingMacros = ref(false);

const macrosForm = useForm({
    protein: props.caloriesOverview.macros?.protein ?? 0,
    carbs: props.caloriesOverview.macros?.carbs ?? 0,
    fats: props.caloriesOverview.macros?.fats ?? 0,
});

const canEditMacros = computed(() => props.can_edit_macros);
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
</script>

<template>
    <Head title="Nutrition" />

    <AppLayout
        title="Nutrition"
        subtitle="Visualisez directement vos calories cibles à partir de votre objectif."
    >
        <div v-if="caloriesOverview.target_calories" class="space-y-4">
            <section class="rounded-lg bg-white p-4">
                <p
                    class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                >
                    Objectif du jour
                </p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div class="space-y-2">
                        <p class="text-5xl font-semibold text-evo-black">
                            {{ caloriesOverview.target_calories }}
                        </p>
                        <p class="text-sm text-neutral-600">
                            kcal à consommer aujourd'hui
                        </p>
                    </div>
                    <Link
                        :href="profile()"
                        class="inline-flex rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:bg-neutral-100"
                    >
                        Modifier mon objectif
                    </Link>
                </div>

                <p class="mt-4 max-w-md text-sm text-neutral-600">
                    Votre objectif calorique est automatiquement calculé à
                    partir de votre objectif actif. Vous n'avez rien à
                    recalculer manuellement.
                </p>
            </section>

            <div :class="ads.enabled ? 'grid gap-4 lg:grid-cols-2' : ''">
                <section class="rounded-lg bg-white p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                            >
                                Progression du jour
                            </p>
                            <p class="mt-2 text-sm text-neutral-600">
                                Aperçu visuel de la consommation du jour.
                            </p>
                        </div>
                        <p class="text-sm font-medium text-evo-purple">
                            {{ progressPercent }}%
                        </p>
                    </div>

                    <div
                        class="mt-4 flex flex-col items-center gap-4 md:flex-row"
                    >
                        <div
                            class="relative flex h-55 w-55 shrink-0 items-center justify-center rounded-full"
                            :style="ringStyle"
                        >
                            <div
                                class="flex h-40 w-40 flex-col items-center justify-center rounded-full bg-white text-center"
                            >
                                <p class="text-3xl font-semibold">
                                    {{ remainingCalories }}
                                </p>
                                <p class="text-xs text-neutral-500">
                                    kcal restantes
                                </p>
                            </div>
                        </div>

                        <div class="w-full flex-1 space-y-3">
                            <div
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="text-sm text-neutral-500">
                                    Consommées
                                </p>
                                <p class="mt-2 text-2xl font-semibold">
                                    {{ consumedCalories }} kcal
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="text-sm text-neutral-500">Objectif</p>
                                <p class="mt-2 text-2xl font-semibold">
                                    {{ targetCalories }} kcal
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <AdInlineSlot
                    :enabled="ads.enabled"
                    variant="square"
                    class="h-full"
                />
            </div>

            <PremiumFeatureGate
                :locked="!canEditMacros"
                feature-name="Gestion des macros nutriment"
                :current-plan="active_subscription_plan"
            >
                <div class="transition">
                    <div class="flex items-center justify-between gap-4">
                        <p
                            class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                        >
                            Gestion des macros nutriment
                        </p>
                        <button
                            v-if="canEditMacros && !isEditingMacros"
                            type="button"
                            class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                            @click="startMacrosEdit"
                        >
                            Modifier mes macros
                        </button>
                    </div>

                    <div
                        v-if="!isEditingMacros"
                        class="mt-4 grid gap-4 sm:grid-cols-3"
                    >
                        <div class="rounded-lg border border-neutral-200 p-4">
                            <p class="text-sm text-neutral-500">Protéines</p>
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

                    <div v-else class="mt-4 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="text-sm text-neutral-500">
                                    Protéines
                                </p>
                                <div
                                    class="mt-3 flex items-center justify-between"
                                >
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                        @click="stepMacro('protein', -1)"
                                    >
                                        -
                                    </button>
                                    <p class="text-2xl font-semibold">
                                        {{ macrosForm.protein }} g
                                    </p>
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                        @click="stepMacro('protein', 1)"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="text-sm text-neutral-500">Glucides</p>
                                <div
                                    class="mt-3 flex items-center justify-between"
                                >
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                        @click="stepMacro('carbs', -1)"
                                    >
                                        -
                                    </button>
                                    <p class="text-2xl font-semibold">
                                        {{ macrosForm.carbs }} g
                                    </p>
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                        @click="stepMacro('carbs', 1)"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="text-sm text-neutral-500">Lipides</p>
                                <div
                                    class="mt-3 flex items-center justify-between"
                                >
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                        @click="stepMacro('fats', -1)"
                                    >
                                        -
                                    </button>
                                    <p class="text-2xl font-semibold">
                                        {{ macrosForm.fats }} g
                                    </p>
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                        @click="stepMacro('fats', 1)"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-neutral-200 bg-neutral-50 p-4"
                        >
                            <p class="text-sm text-neutral-600">
                                Calories estimées avec ces macros
                            </p>
                            <p
                                class="mt-2 text-3xl font-semibold"
                                :class="
                                    isCaloriesOverBase
                                        ? 'text-red-600'
                                        : 'text-emerald-600'
                                "
                            >
                                {{ editedCalories }} kcal
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                Delta vs objectif actuel:
                                {{ caloriesDelta > 0 ? '+' : ''
                                }}{{ caloriesDelta }} kcal
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:cursor-pointer hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="macrosForm.processing"
                                @click="saveMacros"
                            >
                                {{
                                    macrosForm.processing
                                        ? 'Enregistrement...'
                                        : 'Enregistrer'
                                }}
                            </button>
                            <button
                                type="button"
                                class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                                @click="cancelMacrosEdit"
                            >
                                Annuler
                            </button>
                        </div>
                    </div>

                    <p
                        v-if="macroErrorMessage"
                        class="mt-4 text-sm text-red-600"
                    >
                        {{ macroErrorMessage }}
                    </p>
                    <p
                        v-if="flashSuccessMessage"
                        class="mt-4 text-sm text-emerald-700"
                    >
                        {{ flashSuccessMessage }}
                    </p>
                    <p class="mt-4 text-sm text-neutral-600">
                        Ces valeurs viennent directement de votre objectif
                        actif.
                    </p>
                </div>

                <template #locked-preview>
                    <div
                        class="pointer-events-none opacity-35 blur-[5px] select-none"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <p
                                class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                            >
                                Gestion des macros nutriment
                            </p>
                            <div
                                class="h-9 w-32 rounded-full border border-neutral-300"
                            />
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="text-sm text-neutral-500">
                                    Protéines
                                </p>
                                <p class="mt-2 text-2xl font-semibold">-- g</p>
                            </div>
                            <div
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="text-sm text-neutral-500">Glucides</p>
                                <p class="mt-2 text-2xl font-semibold">-- g</p>
                            </div>
                            <div
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="text-sm text-neutral-500">Lipides</p>
                                <p class="mt-2 text-2xl font-semibold">-- g</p>
                            </div>
                        </div>

                        <p class="mt-4 text-sm text-neutral-600">
                            Ces valeurs viennent directement de votre objectif
                            actif.
                        </p>
                    </div>
                </template>
            </PremiumFeatureGate>

            <section class="rounded-lg bg-white p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                        >
                            Saisie des repas
                        </p>
                        <h2 class="mt-2 text-2xl font-semibold">
                            Aperçu V3 non fonctionnel
                        </h2>
                        <p class="mt-2 max-w-md text-sm text-neutral-600">
                            Cette colonne montre simplement à quoi pourrait
                            ressembler l'ajout manuel de calories plus tard,
                            sans logique métier pour le moment.
                        </p>
                    </div>
                    <span
                        class="rounded-full bg-evo-purple/10 px-3 py-1 text-xs font-medium text-evo-purple"
                    >
                        Présentation
                    </span>
                </div>

                <div class="mt-4 grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
                    <div class="rounded-lg border border-neutral-200 p-4">
                        <p class="text-sm font-medium text-neutral-500">
                            Ajouter un repas
                        </p>

                        <div class="mt-4 space-y-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">
                                    Moment du repas
                                </label>
                                <div
                                    class="flex flex-wrap gap-2 text-sm text-evo-black"
                                >
                                    <span
                                        class="rounded-full border border-neutral-300 px-3 py-2"
                                    >
                                        Petit-déjeuner
                                    </span>
                                    <span
                                        class="rounded-full border border-neutral-300 px-3 py-2"
                                    >
                                        Déjeuner
                                    </span>
                                    <span
                                        class="rounded-full bg-evo-black px-3 py-2 text-evo-white"
                                    >
                                        Dîner
                                    </span>
                                    <span
                                        class="rounded-full border border-neutral-300 px-3 py-2"
                                    >
                                        Collation
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium">
                                    Description
                                </label>
                                <div
                                    class="rounded-lg border border-neutral-300 px-4 py-3 text-sm text-neutral-700"
                                >
                                    500 g de riz et 2 morceaux de poulet
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium">
                                        Calories estimées
                                    </label>
                                    <div
                                        class="rounded-lg border border-neutral-300 px-4 py-3 text-sm text-neutral-700"
                                    >
                                        800 kcal
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium">
                                        Heure
                                    </label>
                                    <div
                                        class="rounded-lg border border-neutral-300 px-4 py-3 text-sm text-neutral-700"
                                    >
                                        20:15
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="inline-flex rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white opacity-80 hover:cursor-pointer"
                            >
                                Ajouter ce repas
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div
                            class="rounded-lg border border-neutral-200 bg-neutral-50 p-4"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-neutral-500"
                                    >
                                        Reste après ajout
                                    </p>
                                    <p
                                        class="mt-2 text-3xl font-semibold text-evo-black"
                                    >
                                        {{
                                            Math.max(0, remainingCalories - 800)
                                        }}
                                        kcal
                                    </p>
                                </div>
                                <div
                                    class="rounded-full bg-white px-3 py-1 text-sm font-medium text-evo-purple"
                                >
                                    -800 kcal
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-neutral-200 p-4">
                            <p class="text-sm font-medium text-neutral-500">
                                Journal du jour
                            </p>

                            <div class="mt-4 space-y-3">
                                <div
                                    class="rounded-lg border border-neutral-200 bg-neutral-50 p-4"
                                >
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <div>
                                            <p class="font-medium">
                                                Petit-déjeuner
                                            </p>
                                            <p class="text-sm text-neutral-600">
                                                Porridge, banane, beurre de
                                                cacahuète
                                            </p>
                                        </div>
                                        <p class="font-semibold">520 kcal</p>
                                    </div>
                                </div>

                                <div
                                    class="rounded-lg border border-neutral-200 bg-neutral-50 p-4"
                                >
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <div>
                                            <p class="font-medium">Déjeuner</p>
                                            <p class="text-sm text-neutral-600">
                                                Pâtes, légumes, steak haché
                                            </p>
                                        </div>
                                        <p class="font-semibold">730 kcal</p>
                                    </div>
                                </div>

                                <div
                                    class="rounded-lg border border-evo-purple bg-evo-purple/5 p-4"
                                >
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <div>
                                            <p class="font-medium">Dîner</p>
                                            <p class="text-sm text-neutral-600">
                                                500 g de riz et 2 morceaux de
                                                poulet
                                            </p>
                                        </div>
                                        <p class="font-semibold">800 kcal</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-dashed border-neutral-300 p-4"
                        >
                            <p class="text-sm text-neutral-600">
                                Plus tard, cette zone permettra d'ajouter de
                                vrais aliments, de calculer automatiquement les
                                calories et d'actualiser la progression du jour.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section v-else class="rounded-lg bg-white p-4">
            <h2 class="text-lg font-semibold">Nutrition</h2>
            <p class="mt-3 text-sm text-neutral-600">
                Aucun objectif actif n'est disponible pour calculer vos calories
                du jour.
            </p>
            <Link
                :href="profile()"
                class="mt-4 inline-flex rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:opacity-90"
            >
                Créer ou modifier mon objectif
            </Link>
        </section>
    </AppLayout>
</template>
