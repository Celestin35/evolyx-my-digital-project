<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { progress } from '@/routes';
import {
    activityLevelOptions,
    formatActivityLevelLabel,
    formatSexLabel,
    sexOptions,
} from '@/lib/profile';

type ProfileUser = {
    first_name: string | null;
    sex: string | null;
    height: number | null;
    birth_date: string | null;
    activity_level: string | null;
    age: number | null;
    current_weight: string | number | null;
};

const props = defineProps<{
    user: ProfileUser;
}>();

const isEditing = ref(false);
const isOpen = ref(true);

const personalInfoForm = useForm({
    first_name: props.user.first_name ?? '',
    sex: props.user.sex ?? '',
    height: props.user.height ?? '',
    birth_date: props.user.birth_date ?? '',
    activity_level: props.user.activity_level ?? '',
});

const formattedSex = computed(() => formatSexLabel(props.user.sex));
const formattedActivityLevel = computed(() =>
    formatActivityLevelLabel(props.user.activity_level),
);
const todayDate = computed(() => formatDateInput(new Date()));
const minimumBirthDate = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - 15);

    return formatDateInput(date);
});

function formatDateInput(date: Date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const startEdit = () => {
    personalInfoForm.defaults({
        first_name: props.user.first_name ?? '',
        sex: props.user.sex ?? '',
        height: props.user.height ?? '',
        birth_date: props.user.birth_date ?? '',
        activity_level: props.user.activity_level ?? '',
    });
    personalInfoForm.reset();
    personalInfoForm.clearErrors();
    isEditing.value = true;
};

const cancelEdit = () => {
    personalInfoForm.reset();
    personalInfoForm.clearErrors();
    isEditing.value = false;
};

const savePersonalInfo = () => {
    personalInfoForm.clearErrors('sex', 'height', 'activity_level', 'birth_date');
    const height = Number(personalInfoForm.height);

    if (!['male', 'female', 'other'].includes(personalInfoForm.sex)) {
        personalInfoForm.setError('sex', 'Selectionne ton sexe.');

        return;
    }

    if (
        personalInfoForm.height === '' ||
        !Number.isInteger(height) ||
        height < 50 ||
        height > 300
    ) {
        personalInfoForm.setError(
            'height',
            'La taille doit etre un nombre entier entre 50 et 300 cm.',
        );

        return;
    }

    if (!activityLevelOptions.some((option) => option.value === personalInfoForm.activity_level)) {
        personalInfoForm.setError('activity_level', "Selectionne un niveau d'activite.");

        return;
    }

    if (!personalInfoForm.birth_date) {
        personalInfoForm.setError('birth_date', 'La date de naissance est requise.');

        return;
    }

    if (personalInfoForm.birth_date > todayDate.value) {
        personalInfoForm.setError('birth_date', 'La date de naissance ne peut pas etre dans le futur.');

        return;
    }

    if (personalInfoForm.birth_date > minimumBirthDate.value) {
        personalInfoForm.setError('birth_date', "Tu dois avoir au moins 15 ans pour utiliser l'application.");

        return;
    }

    personalInfoForm.patch('/profile/personal-info', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};
</script>

<template>
    <div class="w-full self-start rounded-lg bg-white p-4">
        <button
            type="button"
            class="flex w-full items-center justify-between text-left hover:cursor-pointer"
            :aria-expanded="isOpen"
            aria-controls="profile-personal-info-content"
            @click="isOpen = !isOpen"
        >
            <h2 class="text-lg font-semibold">Informations personnelles</h2>
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
            <div v-show="isOpen" id="profile-personal-info-content" class="space-y-2 pt-4">
                <div class="flex items-center justify-end">
                    <button
                        v-if="!isEditing"
                        type="button"
                        class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                        @click="startEdit"
                    >
                        Modifier
                    </button>
                </div>

                <template v-if="!isEditing">
                    <div v-if="user.first_name" class="flex items-center gap-1">
                        <p class="font-medium">Prenom :</p>
                        <p>{{ user.first_name }}</p>
                    </div>
                    <div v-if="formattedSex" class="flex items-center gap-1">
                        <p class="font-medium">Sexe :</p>
                        <p>{{ formattedSex }}</p>
                    </div>
                    <div v-if="user.height" class="flex items-center gap-1">
                        <p class="font-medium">Taille :</p>
                        <p>{{ user.height }} cm</p>
                    </div>
                    <div v-if="user.birth_date" class="flex items-center gap-1">
                        <p class="font-medium">Date de naissance :</p>
                        <p>{{ user.birth_date }}</p>
                    </div>
                    <div v-if="formattedActivityLevel" class="flex items-center gap-1">
                        <p class="font-medium">Niveau d'activite :</p>
                        <p>{{ formattedActivityLevel }}</p>
                    </div>
                    <div v-if="user.age" class="flex items-center gap-1">
                        <p class="font-medium">Age :</p>
                        <p>{{ user.age }}</p>
                    </div>
                    <div v-if="user.current_weight" class="flex items-center gap-1">
                        <p class="font-medium">Poids actuel :</p>
                        <p>{{ user.current_weight }} kg</p>
                    </div>
                </template>

                <div v-else class="space-y-4">
                    <div class="space-y-2">
                        <label for="personal_first_name" class="block font-medium">Prenom</label>
                        <input
                            id="personal_first_name"
                            v-model="personalInfoForm.first_name"
                            type="text"
                            class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                        />
                        <p v-if="personalInfoForm.errors.first_name" class="text-sm text-red-600">
                            {{ personalInfoForm.errors.first_name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="personal_sex" class="block font-medium">Sexe</label>
                        <select
                            id="personal_sex"
                            v-model="personalInfoForm.sex"
                            class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                        >
                            <option v-for="option in sexOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <p v-if="personalInfoForm.errors.sex" class="text-sm text-red-600">
                            {{ personalInfoForm.errors.sex }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="personal_height" class="block font-medium">Taille</label>
                        <input
                            id="personal_height"
                            v-model="personalInfoForm.height"
                            type="number"
                            min="50"
                            max="300"
                            class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                        />
                        <p v-if="personalInfoForm.errors.height" class="text-sm text-red-600">
                            {{ personalInfoForm.errors.height }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="personal_birth_date" class="block font-medium">Date de naissance</label>
                        <input
                            id="personal_birth_date"
                            v-model="personalInfoForm.birth_date"
                            type="date"
                            :max="minimumBirthDate"
                            class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                        />
                        <p v-if="personalInfoForm.errors.birth_date" class="text-sm text-red-600">
                            {{ personalInfoForm.errors.birth_date }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="personal_activity_level" class="block font-medium">
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
                        <p v-if="personalInfoForm.errors.activity_level" class="text-sm text-red-600">
                            {{ personalInfoForm.errors.activity_level }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-dashed border-neutral-300 p-4">
                        <p class="text-sm text-neutral-600">
                            Pour modifier ou ajouter une entree de poids, rendez-vous sur votre suivi d'evolution.
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
                            {{ personalInfoForm.processing ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                            @click="cancelEdit"
                        >
                            Annuler
                        </button>
                        <p v-if="personalInfoForm.recentlySuccessful" class="text-sm text-neutral-600">
                            Enregistre.
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
