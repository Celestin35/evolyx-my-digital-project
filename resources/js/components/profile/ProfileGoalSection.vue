<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    formatFrenchDate,
    parseWeight,
    roundToQuarter,
    weeklyGoalOptions,
} from '@/lib/profile';

type ProfileUser = {
    id: number;
    first_name: string | null;
    pseudo: string | null;
    email: string;
    sex: string | null;
    height: number | null;
    birth_date: string | null;
    activity_level: string | null;
    age: number | null;
    current_weight: string | number | null;
    role: string | null;
};

type ActiveGoal = {
    target_weight: string | number | null;
    weekly_weight_goal: string | number | null;
    goal_end_date: string | null;
    goal_type: string | null;
};

const props = defineProps<{
    user: ProfileUser;
    activeGoal: ActiveGoal | null;
}>();

const page = usePage();
const currentWeight = parseWeight(props.user.current_weight);
const initialTargetWeight =
    parseWeight(props.activeGoal?.target_weight ?? null) ??
    (currentWeight === null ? null : roundToQuarter(currentWeight));
const initialWeeklyWeightGoal = parseWeight(
    props.activeGoal?.weekly_weight_goal ?? null,
) ?? 0;

const goalForm = useForm({
    target_weight: initialTargetWeight,
    weekly_weight_goal: initialWeeklyWeightGoal,
});

const isOpen = ref(true);
const isGoalEditorOpen = ref(false);

const formattedTargetWeight = computed(() => {
    if (goalForm.target_weight === null) {
        return null;
    }

    return `${Number(goalForm.target_weight).toFixed(2)} kg`;
});

const goalWeightDelta = computed(() => {
    if (currentWeight === null || goalForm.target_weight === null) {
        return null;
    }

    return Number(goalForm.target_weight) - currentWeight;
});

const selectedGoalType = computed<
    'weight_loss' | 'maintenance' | 'muscle_gain' | null
>(() => {
    if (goalWeightDelta.value === null) {
        return null;
    }

    if (goalWeightDelta.value < 0) {
        return 'weight_loss';
    }

    if (goalWeightDelta.value > 0) {
        return 'muscle_gain';
    }

    return 'maintenance';
});

const selectedGoalTypeLabel = computed(() => {
    return {
        weight_loss: 'Perte de poids',
        maintenance: 'Maintien',
        muscle_gain: 'Prise de masse',
    }[selectedGoalType.value ?? 'maintenance'];
});

const isWeeklyGoalDirectionValid = computed(() => {
    if (selectedGoalType.value === null) {
        return false;
    }

    if (selectedGoalType.value === 'maintenance') {
        return goalForm.weekly_weight_goal === 0;
    }

    if (selectedGoalType.value === 'weight_loss') {
        return goalForm.weekly_weight_goal < 0;
    }

    return goalForm.weekly_weight_goal > 0;
});

const dynamicGoalEndDate = computed(() => {
    if (
        currentWeight === null ||
        goalForm.target_weight === null ||
        selectedGoalType.value === null
    ) {
        return null;
    }

    if (selectedGoalType.value === 'maintenance') {
        return null;
    }

    if (!isWeeklyGoalDirectionValid.value || goalForm.weekly_weight_goal === 0) {
        return null;
    }

    const totalWeightToChange = Math.abs(
        Number(goalForm.target_weight) - currentWeight,
    );
    const weeklyRate = Math.abs(goalForm.weekly_weight_goal);
    const totalDays = Math.max(1, Math.ceil((totalWeightToChange / weeklyRate) * 7));

    const goalDate = new Date();
    goalDate.setDate(goalDate.getDate() + totalDays);

    return goalDate;
});

const formattedGoalEndDate = computed(() =>
    formatFrenchDate(dynamicGoalEndDate.value),
);

const formattedActiveGoalEndDate = computed(() =>
    formatFrenchDate(props.activeGoal?.goal_end_date ?? null),
);

const formattedActiveTargetWeight = computed(() => {
    const targetWeight = parseWeight(props.activeGoal?.target_weight ?? null);

    if (targetWeight === null) {
        return null;
    }

    return `${targetWeight.toFixed(2)} kg`;
});

const successMessage = computed(() => page.props.flash?.success ?? null);
const canSubmitGoal = computed(() => {
    return goalForm.target_weight !== null && isWeeklyGoalDirectionValid.value;
});
const hasActiveGoal = computed(() => props.activeGoal !== null);

const decreaseWeight = () => {
    if (goalForm.target_weight === null) {
        return;
    }

    goalForm.target_weight = Math.max(
        0,
        roundToQuarter(Number(goalForm.target_weight) - 0.25),
    );
};

const increaseWeight = () => {
    if (goalForm.target_weight === null) {
        return;
    }

    goalForm.target_weight = roundToQuarter(Number(goalForm.target_weight) + 0.25);
};

const confirmWeightGoal = () => {
    if (!canSubmitGoal.value) {
        return;
    }

    goalForm.post('/goals', {
        preserveScroll: true,
    });
};

const toggleGoalEditor = () => {
    isGoalEditorOpen.value = !isGoalEditorOpen.value;
};
</script>

<template>
    <div class="self-start w-full rounded-lg bg-evo-white p-4">
        <button
            type="button"
            class="flex w-full items-center justify-between text-left hover:cursor-pointer"
            :aria-expanded="isOpen"
            aria-controls="profile-goal-content"
            @click="isOpen = !isOpen"
        >
            <h2 class="text-lg font-semibold">Objectif de poids</h2>
            <ChevronDown
                class="h-6 w-6 text-evo-black transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
                aria-hidden="true"
            />
        </button>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-1 opacity-0"
        >
            <div
                v-show="isOpen"
                id="profile-goal-content"
                class="space-y-4 pt-4"
            >
                <div
                    v-if="successMessage"
                    class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700"
                >
                    {{ successMessage }}
                </div>

                <div class="space-y-1">
                    <p>
                        Poids actuel :
                        <span class="font-semibold">
                            {{ user.current_weight }} kg
                        </span>
                    </p>
                </div>

                <div
                    v-if="hasActiveGoal"
                    class="rounded-lg border border-neutral-200 p-4 bg-white"
                >
                    <p class="font-medium">Objectif en cours</p>
                    <div class="mt-3 space-y-2 text-sm text-neutral-700">
                        <p>
                            Type :
                            <span class="font-semibold text-evo-black">
                                {{ activeGoal?.goal_type ?? 'Non défini' }}
                            </span>
                        </p>
                        <p>
                            Poids cible :
                            <span class="font-semibold text-evo-black">
                                {{ formattedActiveTargetWeight ?? 'Non défini' }}
                            </span>
                        </p>
                        <p>
                            Date de fin estimée :
                            <span class="font-semibold text-evo-black">
                                {{
                                    formattedActiveGoalEndDate ??
                                    'Pas de date de fin'
                                }}
                            </span>
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-lg border border-dashed border-neutral-300 p-4"
                >
                    <p class="font-medium">Pas d'objectif en cours.</p>
                    <p class="mt-1 text-sm text-neutral-600">
                        Créez un objectif pour définir votre poids cible et votre
                        rythme.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        type="button"
                        :variant="isGoalEditorOpen ? 'transparent' : 'default'"
                        @click="toggleGoalEditor"
                    >
                        {{
                            isGoalEditorOpen
                                ? 'Fermer'
                                : hasActiveGoal
                                  ? 'Modifier'
                                  : 'Créer un objectif'
                        }}
                    </Button>
                </div>

                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="-translate-y-1 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="-translate-y-1 opacity-0"
                >
                    <div v-show="isGoalEditorOpen">
                    <div class="js-goal-editor-inner space-y-4 pt-4">
                        <div
                            v-if="goalForm.target_weight !== null"
                            class="flex flex-col gap-4"
                        >
                            <p class="font-medium">Poids cible :</p>

                            <div class="flex items-center gap-4">
                                <Button
                                    type="button"
                                    class="size-9 rounded-full p-2"
                                    @click="decreaseWeight"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M6.00001 11.25L18 11.25L18 12.75L6.00001 12.75L6.00001 11.25Z"
                                            fill="currentColor"
                                        />
                                    </svg>
                                </Button>
                                <p class="min-w-24 text-center text-lg font-semibold">
                                    {{ formattedTargetWeight }}
                                </p>
                                <Button
                                    type="button"
                                    class="size-9 rounded-full p-2"
                                    @click="increaseWeight"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="currentColor"
                                        viewBox="0 0 32 32"
                                    >
                                        <path
                                            d="M15 5L15 15L5 15L5 17L15 17L15 27L17 27L17 17L27 17L27 15L17 15L17 5Z"
                                        />
                                    </svg>
                                </Button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label
                                for="weekly_weight_goal"
                                class="block font-medium"
                            >
                                Rythme hebdomadaire
                            </label>

                            <div
                                class="w-fit rounded-md border border-evo-black bg-evo-white px-4 py-2 focus-within:ring-1 focus-within:ring-evo-purple"
                            >
                                <select
                                    id="weekly_weight_goal"
                                    v-model.number="goalForm.weekly_weight_goal"
                                    class="bg-transparent focus:outline-none"
                                >
                                    <option
                                        v-for="option in weeklyGoalOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <p v-if="selectedGoalType" class="font-medium">
                                Type d'objectif :
                                {{ selectedGoalTypeLabel }}
                            </p>

                            <p
                                v-if="
                                    selectedGoalType === 'maintenance' &&
                                    goalForm.weekly_weight_goal === 0
                                "
                                class="text-sm text-neutral-600"
                            >
                                Pas de date de fin pour un objectif de maintien.
                            </p>
                            <p
                                v-else-if="formattedGoalEndDate"
                                class="text-sm text-neutral-600"
                            >
                                Date de fin estimée : {{ formattedGoalEndDate }}
                            </p>
                            <p
                                v-else-if="!isWeeklyGoalDirectionValid"
                                class="text-sm text-red-600"
                            >
                                Le rythme hebdomadaire doit correspondre au sens
                                de l'objectif.
                            </p>

                            <p
                                v-if="goalForm.errors.target_weight"
                                class="text-sm text-red-600"
                            >
                                {{ goalForm.errors.target_weight }}
                            </p>
                            <p
                                v-if="goalForm.errors.weekly_weight_goal"
                                class="text-sm text-red-600"
                            >
                                {{ goalForm.errors.weekly_weight_goal }}
                            </p>

                            <Button
                                type="button"
                                :disabled="!canSubmitGoal || goalForm.processing"
                                @click="confirmWeightGoal"
                            >
                                {{
                                    goalForm.processing
                                        ? 'Enregistrement...'
                                        : 'Confirmer'
                                }}
                            </Button>
                        </div>
                    </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>
