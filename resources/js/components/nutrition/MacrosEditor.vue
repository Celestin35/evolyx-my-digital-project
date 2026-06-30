<script setup lang="ts">
import PremiumFeatureGate from '@/components/PremiumFeatureGate.vue';
import { Button } from '@/components/ui/button';
import type { CaloriesOverview, MacrosField } from '@/types/nutrition';

defineProps<{
    caloriesOverview: CaloriesOverview;
    canEditMacros: boolean;
    activeSubscriptionPlan: string | null;
    isEditingMacros: boolean;
    macrosForm: {
        protein: number;
        carbs: number;
        fats: number;
        processing: boolean;
    };
    editedCalories: number;
    caloriesDelta: number;
    isCaloriesOverBase: boolean;
    macroErrorMessage?: string;
    flashSuccessMessage?: string;
}>();

const emit = defineEmits<{
    startEdit: [];
    cancelEdit: [];
    save: [];
    stepMacro: [field: MacrosField, delta: number];
}>();
</script>

<template>
    <PremiumFeatureGate
        :locked="!canEditMacros"
        feature-name="Gestion des macros nutriment"
        :current-plan="activeSubscriptionPlan"
        class="h-full"
    >
        <div class="transition">
            <div class="flex items-center justify-between gap-4">
                <p
                    class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                >
                    Gestion des macros nutriment
                </p>
                <Button
                    v-if="canEditMacros && !isEditingMacros"
                    type="button"
                    @click="emit('startEdit')"
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
                    <div
                        class="rounded-lg border border-neutral-200 bg-white p-4"
                    >
                        <p class="text-sm text-neutral-500">Proteines</p>
                        <div class="mt-3 flex items-center justify-between">
                            <button
                                type="button"
                                class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                @click="emit('stepMacro', 'protein', -1)"
                            >
                                -
                            </button>
                            <p class="text-2xl font-semibold">
                                {{ macrosForm.protein }} g
                            </p>
                            <button
                                type="button"
                                class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                @click="emit('stepMacro', 'protein', 1)"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-neutral-200 bg-white p-4"
                    >
                        <p class="text-sm text-neutral-500">Glucides</p>
                        <div class="mt-3 flex items-center justify-between">
                            <button
                                type="button"
                                class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                @click="emit('stepMacro', 'carbs', -1)"
                            >
                                -
                            </button>
                            <p class="text-2xl font-semibold">
                                {{ macrosForm.carbs }} g
                            </p>
                            <button
                                type="button"
                                class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                @click="emit('stepMacro', 'carbs', 1)"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-neutral-200 bg-white p-4"
                    >
                        <p class="text-sm text-neutral-500">Lipides</p>
                        <div class="mt-3 flex items-center justify-between">
                            <button
                                type="button"
                                class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                @click="emit('stepMacro', 'fats', -1)"
                            >
                                -
                            </button>
                            <p class="text-2xl font-semibold">
                                {{ macrosForm.fats }} g
                            </p>
                            <button
                                type="button"
                                class="h-9 w-9 rounded-full border border-neutral-300 text-lg leading-none hover:cursor-pointer hover:bg-neutral-100"
                                @click="emit('stepMacro', 'fats', 1)"
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
                        Calories estimees avec ces macros
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
                        {{ caloriesDelta > 0 ? '+' : '' }}{{ caloriesDelta }}
                        kcal
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        type="button"
                        :disabled="macrosForm.processing"
                        @click="emit('save')"
                    >
                        {{
                            macrosForm.processing
                                ? 'Enregistrement...'
                                : 'Enregistrer'
                        }}
                    </Button>
                    <Button
                        type="button"
                        variant="transparent"
                        @click="emit('cancelEdit')"
                    >
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
                        class="rounded-lg border border-neutral-200 bg-white p-4"
                    >
                        <p class="text-sm text-neutral-500">Proteines</p>
                        <p class="mt-2 text-2xl font-semibold">-- g</p>
                    </div>
                    <div
                        class="rounded-lg border border-neutral-200 bg-white p-4"
                    >
                        <p class="text-sm text-neutral-500">Glucides</p>
                        <p class="mt-2 text-2xl font-semibold">-- g</p>
                    </div>
                    <div
                        class="rounded-lg border border-neutral-200 bg-white p-4"
                    >
                        <p class="text-sm text-neutral-500">Lipides</p>
                        <p class="mt-2 text-2xl font-semibold">-- g</p>
                    </div>
                </div>
            </div>
        </template>
    </PremiumFeatureGate>
</template>
