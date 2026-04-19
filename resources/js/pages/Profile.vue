<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { progress } from '@/routes';
import gsap from 'gsap';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import arrowDown from '../../images/icons/arrow-down-purple.svg';

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
const WEIGHT_STEP = 0.25;

const weeklyGoalOptions = [
    { value: -1, label: 'Perdre 1 kg par semaine' },
    { value: -0.75, label: 'Perdre 0.75 kg par semaine' },
    { value: -0.5, label: 'Perdre 0.5 kg par semaine' },
    { value: -0.25, label: 'Perdre 0.25 kg par semaine' },
    { value: 0, label: 'Maintenir mon poids' },
    { value: 0.25, label: 'Gagner 0.25 kg par semaine' },
    { value: 0.5, label: 'Gagner 0.5 kg par semaine' },
    { value: 0.75, label: 'Gagner 0.75 kg par semaine' },
    { value: 1, label: 'Gagner 1 kg par semaine' },
];

const sexOptions = [
    { value: 'male', label: 'Homme' },
    { value: 'female', label: 'Femme' },
    { value: 'other', label: 'Autre' },
];

const activityLevelOptions = [
    { value: 'sedentary', label: 'Sedentaire' },
    { value: 'light', label: 'Leger' },
    { value: 'moderate', label: 'Modere' },
    { value: 'active', label: 'Actif' },
    { value: 'very_active', label: 'Tres actif' },
];

const parseWeight = (value: string | number | null): number | null => {
    if (value === null || value === '') {
        return null;
    }

    const parsedValue =
        typeof value === 'number' ? value : Number.parseFloat(value);

    if (Number.isNaN(parsedValue)) {
        return null;
    }

    return parsedValue;
};

const roundToQuarter = (value: number): number =>
    Math.round(value / WEIGHT_STEP) * WEIGHT_STEP;

const currentWeight = parseWeight(props.user.current_weight);
const initialTargetWeight =
    parseWeight(props.activeGoal?.target_weight ?? null) ??
    (currentWeight === null ? null : roundToQuarter(currentWeight));
const initialWeeklyWeightGoal = parseWeight(
    props.activeGoal?.weekly_weight_goal ?? null,
) ?? 0;

const personalInfoForm = useForm({
    first_name: props.user.first_name ?? '',
    sex: props.user.sex ?? '',
    height: props.user.height ?? '',
    birth_date: props.user.birth_date ?? '',
    activity_level: props.user.activity_level ?? '',
});

const goalForm = useForm({
    target_weight: initialTargetWeight,
    weekly_weight_goal: initialWeeklyWeightGoal,
});

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

const formattedGoalEndDate = computed(() => {
    if (dynamicGoalEndDate.value === null) {
        return null;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(dynamicGoalEndDate.value);
});

const formattedActiveGoalEndDate = computed(() => {
    if (!props.activeGoal?.goal_end_date) {
        return null;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(props.activeGoal.goal_end_date));
});

const formattedActiveTargetWeight = computed(() => {
    const targetWeight = parseWeight(props.activeGoal?.target_weight ?? null);

    if (targetWeight === null) {
        return null;
    }

    return `${targetWeight.toFixed(2)} kg`;
});

const formattedSex = computed(() => {
    return (
        {
            male: 'Homme',
            female: 'Femme',
            other: 'Autre',
        }[props.user.sex ?? ''] ?? props.user.sex
    );
});

const formattedActivityLevel = computed(() => {
    return (
        {
            sedentary: 'Sedentaire',
            light: 'Leger',
            moderate: 'Modere',
            active: 'Actif',
            very_active: 'Tres actif',
        }[props.user.activity_level ?? ''] ?? props.user.activity_level
    );
});

const successMessage = computed(() => page.props.flash?.success ?? null);
const canSubmitGoal = computed(() => {
    return goalForm.target_weight !== null && isWeeklyGoalDirectionValid.value;
});
const hasActiveGoal = computed(() => props.activeGoal !== null);

const isPersonalInfoEditing = ref(false);
const isGoalEditorOpen = ref(false);
const sectionsRoot = ref<HTMLElement | null>(null);
const goalEditor = ref<HTMLElement | null>(null);
let goalEditorTimeline: gsap.core.Timeline | null = null;
const cleanups: Array<() => void> = [];

const startPersonalInfoEdit = () => {
    personalInfoForm.defaults({
        first_name: props.user.first_name ?? '',
        sex: props.user.sex ?? '',
        height: props.user.height ?? '',
        birth_date: props.user.birth_date ?? '',
        activity_level: props.user.activity_level ?? '',
    });
    personalInfoForm.reset();
    personalInfoForm.clearErrors();
    isPersonalInfoEditing.value = true;
};

const cancelPersonalInfoEdit = () => {
    personalInfoForm.reset();
    personalInfoForm.clearErrors();
    isPersonalInfoEditing.value = false;
};

const savePersonalInfo = () => {
    personalInfoForm.patch('/profile/personal-info', {
        preserveScroll: true,
        onSuccess: () => {
            isPersonalInfoEditing.value = false;
        },
    });
};

const decreaseWeight = () => {
    if (goalForm.target_weight === null) {
        return;
    }

    goalForm.target_weight = Math.max(
        0,
        roundToQuarter(Number(goalForm.target_weight) - WEIGHT_STEP),
    );
};

const increaseWeight = () => {
    if (goalForm.target_weight === null) {
        return;
    }

    goalForm.target_weight = roundToQuarter(
        Number(goalForm.target_weight) + WEIGHT_STEP,
    );
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
    if (!goalEditorTimeline) {
        return;
    }

    if (goalEditorTimeline.reversed() || goalEditorTimeline.progress() === 0) {
        isGoalEditorOpen.value = true;
        goalEditorTimeline.play();
        return;
    }

    isGoalEditorOpen.value = false;
    goalEditorTimeline.reverse();
};

onMounted(() => {
    const root = sectionsRoot.value;
    if (!root) {
        return;
    }

    const sections = root.querySelectorAll<HTMLElement>('.js-section');

    sections.forEach((section) => {
        const trigger = section.querySelector<HTMLElement>('.js-section-trigger');
        const content = section.querySelector<HTMLElement>('.js-section-content');

        if (!trigger || !content) {
            return;
        }

        const tl = gsap.timeline({ paused: true });

        tl.to(content, {
            height: 'auto',
            opacity: 1,
            duration: 0.4,
            ease: 'power2.out',
        }).to(
            trigger.querySelector('.js-open-arrow'),
            {
                rotate: 180,
                duration: 0.4,
                ease: 'power2.out',
            },
            0,
        );

        const handleClick = () => {
            if (tl.reversed() || tl.progress() === 0) {
                tl.play();
                return;
            }

            tl.reverse();
        };

        gsap.set(content, { height: 0, opacity: 0, overflow: 'hidden' });
        trigger.addEventListener('click', handleClick);

        if (section.dataset.defaultOpen === 'true') {
            tl.play(0);
        } else {
            tl.reverse(0);
        }

        cleanups.push(() => {
            trigger.removeEventListener('click', handleClick);
            tl.kill();
        });
    });

    if (goalEditor.value) {
        goalEditorTimeline = gsap.timeline({ paused: true });

        goalEditorTimeline
            .to(
                goalEditor.value,
                {
                    display: 'block',
                    duration: 0,
                },
                0,
            )
            .to(
                goalEditor.value,
                {
                    display: 'block',
                    autoAlpha: 1,
                    duration: 0.25,
                    ease: 'power2.out',
                },
                0,
            )
            .reverse(0);

        cleanups.push(() => goalEditorTimeline?.kill());
    }
});

onBeforeUnmount(() => {
    cleanups.forEach((cleanup) => cleanup());
    cleanups.length = 0;
    goalEditorTimeline = null;
});
</script>

<template>
    <Head title="Profil" />

    <AppLayout title="Profil" subtitle="Gerez vos informations personnelles.">
        <section ref="sectionsRoot" class="flex flex-col gap-4 lg:flex-row">
            <div class="flex w-full flex-col gap-4 lg:w-1/2">
                <div
                    data-default-open="true"
                    class="js-section self-start w-full rounded-lg bg-white p-6"
                >
                    <button
                        type="button"
                        class="js-section-trigger flex w-full items-center justify-between text-left hover:cursor-pointer"
                    >
                        <h2 class="text-lg font-semibold">
                            Informations personnelles
                        </h2>
                        <span>
                            <img
                                :src="arrowDown"
                                alt="Fleche pour ouvrir"
                                class="js-open-arrow h-auto w-6 rotate-0"
                            />
                        </span>
                    </button>

                    <div class="js-section-content">
                        <div class="space-y-2 pt-4">
                            <div class="flex items-center justify-end">
                                <button
                                    v-if="!isPersonalInfoEditing"
                                    type="button"
                                    class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                                    @click="startPersonalInfoEdit"
                                >
                                    Modifier
                                </button>
                            </div>

                            <template v-if="!isPersonalInfoEditing">
                                <div
                                    v-if="user.first_name"
                                    class="flex items-center gap-1"
                                >
                                    <p class="font-medium">Prenom :</p>
                                    <p>{{ user.first_name }}</p>
                                </div>
                                <div
                                    v-if="formattedSex"
                                    class="flex items-center gap-1"
                                >
                                    <p class="font-medium">Sexe :</p>
                                    <p>{{ formattedSex }}</p>
                                </div>
                                <div
                                    v-if="user.height"
                                    class="flex items-center gap-1"
                                >
                                    <p class="font-medium">Taille :</p>
                                    <p>{{ user.height }} cm</p>
                                </div>
                                <div
                                    v-if="user.birth_date"
                                    class="flex items-center gap-1"
                                >
                                    <p class="font-medium">Date de naissance :</p>
                                    <p>{{ user.birth_date }}</p>
                                </div>
                                <div
                                    v-if="formattedActivityLevel"
                                    class="flex items-center gap-1"
                                >
                                    <p class="font-medium">Niveau d'activite :</p>
                                    <p>{{ formattedActivityLevel }}</p>
                                </div>
                                <div v-if="user.age" class="flex items-center gap-1">
                                    <p class="font-medium">Age :</p>
                                    <p>{{ user.age }}</p>
                                </div>
                                <div
                                    v-if="user.current_weight"
                                    class="flex items-center gap-1"
                                >
                                    <p class="font-medium">Poids actuel :</p>
                                    <p>{{ user.current_weight }} kg</p>
                                </div>
                            </template>

                            <div v-else class="space-y-4">
                                <div class="space-y-2">
                                    <label
                                        for="personal_first_name"
                                        class="block font-medium"
                                    >
                                        Prenom
                                    </label>
                                    <input
                                        id="personal_first_name"
                                        v-model="personalInfoForm.first_name"
                                        type="text"
                                        class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                                    />
                                    <p
                                        v-if="personalInfoForm.errors.first_name"
                                        class="text-sm text-red-600"
                                    >
                                        {{ personalInfoForm.errors.first_name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        for="personal_sex"
                                        class="block font-medium"
                                    >
                                        Sexe
                                    </label>
                                    <select
                                        id="personal_sex"
                                        v-model="personalInfoForm.sex"
                                        class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                                    >
                                        <option
                                            v-for="option in sexOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="personalInfoForm.errors.sex"
                                        class="text-sm text-red-600"
                                    >
                                        {{ personalInfoForm.errors.sex }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        for="personal_height"
                                        class="block font-medium"
                                    >
                                        Taille
                                    </label>
                                    <input
                                        id="personal_height"
                                        v-model="personalInfoForm.height"
                                        type="number"
                                        min="100"
                                        max="250"
                                        class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                                    />
                                    <p
                                        v-if="personalInfoForm.errors.height"
                                        class="text-sm text-red-600"
                                    >
                                        {{ personalInfoForm.errors.height }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        for="personal_birth_date"
                                        class="block font-medium"
                                    >
                                        Date de naissance
                                    </label>
                                    <input
                                        id="personal_birth_date"
                                        v-model="personalInfoForm.birth_date"
                                        type="date"
                                        class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                                    />
                                    <p
                                        v-if="personalInfoForm.errors.birth_date"
                                        class="text-sm text-red-600"
                                    >
                                        {{ personalInfoForm.errors.birth_date }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        for="personal_activity_level"
                                        class="block font-medium"
                                    >
                                        Niveau d'activite
                                    </label>
                                    <select
                                        id="personal_activity_level"
                                        v-model="personalInfoForm.activity_level"
                                        class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                                    >
                                        <option
                                            v-for="option in activityLevelOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="personalInfoForm.errors.activity_level"
                                        class="text-sm text-red-600"
                                    >
                                        {{ personalInfoForm.errors.activity_level }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-lg border border-dashed border-neutral-300 p-4"
                                >
                                    <p class="text-sm text-neutral-600">
                                        Pour modifier ou ajouter une entree de poids,
                                        rendez-vous sur votre suivi d'evolution.
                                    </p>
                                    <Link
                                        :href="progress()"
                                        class="mt-3 inline-flex rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:cursor-pointer hover:opacity-90"
                                    >
                                        Gerer mes entrees de poids
                                    </Link>
                                </div>

                                <div class="flex items-center gap-3 pt-2">
                                    <button
                                        type="button"
                                        class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:cursor-pointer hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                        :disabled="personalInfoForm.processing"
                                        @click="savePersonalInfo"
                                    >
                                        {{
                                            personalInfoForm.processing
                                                ? 'Enregistrement...'
                                                : 'Enregistrer'
                                        }}
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                                        @click="cancelPersonalInfoEdit"
                                    >
                                        Annuler
                                    </button>
                                    <p
                                        v-if="personalInfoForm.recentlySuccessful"
                                        class="text-sm text-neutral-600"
                                    >
                                        Enregistre.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex w-full flex-col gap-4 lg:w-1/2">
                <div
                    data-default-open="true"
                    class="js-section self-start w-full rounded-lg bg-white p-6"
                >
                    <button
                        type="button"
                        class="js-section-trigger flex w-full items-center justify-between text-left hover:cursor-pointer"
                    >
                        <h2 class="text-lg font-semibold">Objectif de poids</h2>
                        <span>
                            <img
                                :src="arrowDown"
                                alt="Fleche pour ouvrir"
                                class="js-open-arrow h-auto w-6 rotate-0"
                            />
                        </span>
                    </button>

                    <div class="js-section-content">
                        <div class="space-y-5 pt-4">
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
                                class="rounded-lg border border-neutral-200 p-4"
                            >
                                <p class="font-medium">Objectif en cours</p>
                                <div class="mt-3 space-y-2 text-sm text-neutral-700">
                                    <p>
                                        Type :
                                        <span class="font-semibold text-evo-black">
                                            {{ activeGoal?.goal_type ?? 'Non defini' }}
                                        </span>
                                    </p>
                                    <p>
                                        Poids cible :
                                        <span class="font-semibold text-evo-black">
                                            {{ formattedActiveTargetWeight ?? 'Non defini' }}
                                        </span>
                                    </p>
                                    <p>
                                        Date de fin estimee :
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
                                    Creez un objectif pour definir votre poids cible
                                    et votre rythme.
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:cursor-pointer hover:opacity-90"
                                    @click="toggleGoalEditor"
                                >
                                    {{
                                        isGoalEditorOpen
                                            ? 'Fermer'
                                            : hasActiveGoal
                                              ? 'Modifier'
                                              : 'Creer un objectif'
                                    }}
                                </button>
                            </div>

                            <div
                                ref="goalEditor"
                                class="js-goal-editor hidden invisible opacity-0"
                            >
                                <div class="js-goal-editor-inner space-y-5 pt-5">
                                    <div
                                        v-if="goalForm.target_weight !== null"
                                        class="flex flex-col gap-4"
                                    >
                                        <p class="font-medium">Poids cible :</p>

                                        <div class="flex items-center gap-4">
                                            <button
                                                type="button"
                                                class="flex h-9 w-9 items-center justify-center rounded-full bg-evo-black p-2 text-xl leading-none text-evo-white transition-all duration-300 ease-in-out hover:cursor-pointer hover:opacity-70"
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
                                            </button>
                                            <p class="min-w-24 text-center text-lg font-semibold">
                                                {{ formattedTargetWeight }}
                                            </p>
                                            <button
                                                type="button"
                                                class="flex h-9 w-9 items-center justify-center rounded-full bg-evo-black p-2 text-xl leading-none text-evo-white transition-all duration-300 ease-in-out hover:cursor-pointer hover:opacity-70"
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
                                            </button>
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
                                            Pas de date de fin pour un objectif de
                                            maintien.
                                        </p>
                                        <p
                                            v-else-if="formattedGoalEndDate"
                                            class="text-sm text-neutral-600"
                                        >
                                            Date de fin estimee :
                                            {{ formattedGoalEndDate }}
                                        </p>
                                        <p
                                            v-else-if="!isWeeklyGoalDirectionValid"
                                            class="text-sm text-red-600"
                                        >
                                            Le rythme hebdomadaire doit correspondre
                                            au sens de l'objectif.
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

                                        <button
                                            type="button"
                                            class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:cursor-pointer hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="!canSubmitGoal || goalForm.processing"
                                            @click="confirmWeightGoal"
                                        >
                                            {{
                                                goalForm.processing
                                                    ? 'Enregistrement...'
                                                    : 'Confirmer'
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
